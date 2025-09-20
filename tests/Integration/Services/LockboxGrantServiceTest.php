<?php

declare(strict_types=1);

namespace N3XT0R\FilamentLockbox\Tests\Integration\Services;

use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use N3XT0R\FilamentLockbox\Contracts\HasLockboxKeys;
use N3XT0R\FilamentLockbox\Contracts\UserKeyMaterialProviderInterface;
use N3XT0R\FilamentLockbox\Events\LockboxGrantRevoked;
use N3XT0R\FilamentLockbox\Managers\LockboxManager;
use N3XT0R\FilamentLockbox\Models\Lockbox;
use N3XT0R\FilamentLockbox\Models\LockboxAudit;
use N3XT0R\FilamentLockbox\Models\LockboxGroup;
use N3XT0R\FilamentLockbox\Resolvers\UserKeyMaterialResolver;
use N3XT0R\FilamentLockbox\Services\LockboxGrantService;
use N3XT0R\FilamentLockbox\Tests\TestCase;

final class TestKeyMaterialProvider implements UserKeyMaterialProviderInterface
{
    public function supports(BaseUser $user): bool
    {
        return $user instanceof TestLockboxUser;
    }

    public function provide(BaseUser $user, ?string $input): string
    {
        return 'client-key-' . $user->getKey();
    }
}

/**
 * @extends BaseUser<array<string, mixed>>
 */
final class TestLockboxUser extends BaseUser implements HasLockboxKeys
{
    protected $guarded = [];

    protected $table = 'users';

    public function getEncryptedUserKey(): ?string
    {
        return $this->getAttribute('encrypted_user_key');
    }

    public function setEncryptedUserKey(string $value): void
    {
        $this->setAttribute('encrypted_user_key', $value);
    }

    public function getCryptoPasswordHash(): ?string
    {
        return $this->getAttribute('crypto_password_hash');
    }

    public function setCryptoPasswordHash(string $hash): void
    {
        $this->setAttribute('crypto_password_hash', $hash);
    }

    public function getLockboxProvider(): ?string
    {
        return $this->getAttribute('lockbox_provider');
    }

    public function setLockboxProvider(string $provider): void
    {
        $this->setAttribute('lockbox_provider', $provider);
    }

    public function initializeUserKeyIfMissing(): void
    {
        // not needed for tests
    }

    public function setCryptoPassword(string $plainPassword): void
    {
        $this->setAttribute('crypto_password_hash', bcrypt($plainPassword));
    }
}

class LockboxGrantServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        config(['auth.providers.users.model' => TestLockboxUser::class]);

        app(UserKeyMaterialResolver::class)->registerProvider(new TestKeyMaterialProvider());
    }

    public function testRevokingGrantWithoutRotationRemovesGrantWithoutChangingDekVersion(): void
    {
        $owner = $this->createUser('Owner');
        $recipient = $this->createUser('Recipient');
        $lockbox = $this->createLockbox($owner);

        /** @var LockboxGrantService $service */
        $service = app(LockboxGrantService::class);

        $this->actingAs($owner);
        $grant = $service->shareWithUser($lockbox, $recipient);
        $grant->setRelation('lockbox', $lockbox);
        $initialVersion = $lockbox->fresh()->getAttribute('dek_version');

        $service->revokeGrant($grant, false);

        $this->assertDatabaseMissing('lockbox_grants', ['id' => $grant->getKey()]);
        $this->assertSame($initialVersion, $lockbox->fresh()->getAttribute('dek_version'));
    }

    public function testRevokingGrantWithRotationUpdatesDekAndRewrapsRemainingGrants(): void
    {
        $owner = $this->createUser('Owner');
        $groupMember = $this->createUser('GroupMember');
        $revokedUser = $this->createUser('Revokee');
        $lockbox = $this->createLockbox($owner);
        $group = $this->createGroup($owner, $groupMember);

        /** @var LockboxGrantService $service */
        $service = app(LockboxGrantService::class);

        $this->actingAs($owner);
        $groupGrant = $service->shareWithGroup($lockbox, $group);
        $groupGrant->setRelation('lockbox', $lockbox);
        $userGrant = $service->shareWithUser($lockbox, $revokedUser);
        $userGrant->setRelation('lockbox', $lockbox);

        $initialVersion = $lockbox->fresh()->getAttribute('dek_version');
        $initialEncryptedDek = $lockbox->getAttribute('encrypted_dek');
        $initialGroupWrap = $groupGrant->getAttribute('wrapped_dek');

        $service->revokeGrant($userGrant, true);

        $lockbox->refresh();
        $groupGrant->refresh();

        $this->assertDatabaseMissing('lockbox_grants', ['id' => $userGrant->getKey()]);
        $this->assertSame($initialVersion + 1, $lockbox->getAttribute('dek_version'));
        $this->assertNotSame($initialEncryptedDek, $lockbox->getAttribute('encrypted_dek'));
        $this->assertSame($lockbox->getAttribute('dek_version'), $groupGrant->getAttribute('dek_version'));
        $this->assertNotSame($initialGroupWrap, $groupGrant->getAttribute('wrapped_dek'));
    }

    public function testRevokingGrantRecordsAuditAndDispatchesEvent(): void
    {
        $owner = $this->createUser('Owner');
        $recipient = $this->createUser('Recipient');
        $lockbox = $this->createLockbox($owner);

        /** @var LockboxGrantService $service */
        $service = app(LockboxGrantService::class);

        $this->actingAs($owner);
        $grant = $service->shareWithUser($lockbox, $recipient);
        $grant->setRelation('lockbox', $lockbox);

        Event::fake([LockboxGrantRevoked::class]);

        $service->revokeGrant($grant, false);

        Event::assertDispatched(LockboxGrantRevoked::class, function (LockboxGrantRevoked $event) use ($lockbox, $grant, $owner): bool {
            return $event->lockbox->is($lockbox->fresh())
                && $event->grant->getKey() === $grant->getKey()
                && $event->actor->is($owner);
        });

        $audit = LockboxAudit::query()
            ->where('lockbox_id', $lockbox->getKey())
            ->where('event', 'revoke')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($recipient->getKey(), $audit->getAttribute('context')['grantee_id'] ?? null);
    }

    private function createUser(string $name): TestLockboxUser
    {
        $user = new TestLockboxUser();
        $user->forceFill([
            'name' => $name,
            'email' => Str::uuid() . '@example.com',
            'password' => bcrypt('password'),
            'lockbox_provider' => TestKeyMaterialProvider::class,
        ])->save();

        $user->forceFill([
            'encrypted_user_key' => Crypt::encryptString('server-key-' . $user->getKey()),
        ])->save();

        return $user->fresh();
    }

    private function createLockbox(TestLockboxUser $owner): Lockbox
    {
        /** @var LockboxManager $manager */
        $manager = app(LockboxManager::class);
        $encrypter = $manager->forUser($owner);

        $dek = base64_encode(random_bytes(32));

        $lockbox = new Lockbox();
        $lockbox->forceFill([
            'user_id' => $owner->getKey(),
            'lockboxable_type' => TestLockboxUser::class,
            'lockboxable_id' => $owner->getKey(),
            'name' => 'secret-' . Str::random(8),
            'value' => 'ciphertext',
            'encrypted_dek' => $encrypter->encrypt($dek),
            'dek_version' => 1,
        ])->save();

        $lockbox->refresh();
        $lockbox->setRelation('user', $owner);

        return $lockbox;
    }

    private function createGroup(TestLockboxUser $owner, TestLockboxUser $member): LockboxGroup
    {
        /** @var LockboxManager $manager */
        $manager = app(LockboxManager::class);
        $ownerEncrypter = $manager->forUser($owner);
        $memberEncrypter = $manager->forUser($member);

        $groupKey = random_bytes(32);

        $group = LockboxGroup::query()->create([
            'name' => 'Team ' . Str::random(6),
            'encrypted_group_key' => Crypt::encryptString(base64_encode($groupKey)),
            'created_by' => $owner->getKey(),
        ]);

        $timestamps = ['created_at' => now(), 'updated_at' => now()];

        DB::table('lockbox_group_user')->insert(array_merge([
            'group_id' => $group->getKey(),
            'user_id' => $owner->getKey(),
            'wrapped_group_key_for_user' => $ownerEncrypter->encrypt($groupKey),
        ], $timestamps));

        DB::table('lockbox_group_user')->insert(array_merge([
            'group_id' => $group->getKey(),
            'user_id' => $member->getKey(),
            'wrapped_group_key_for_user' => $memberEncrypter->encrypt($groupKey),
        ], $timestamps));

        return $group;
    }
}
