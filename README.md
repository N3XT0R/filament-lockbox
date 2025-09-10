# Filament v4 security addon to protect sensitive data with user-bound encryption keys (Split-Key, TOTP, or crypto password)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/n3xt0r/filament-lockbox.svg?style=flat-square)](https://packagist.org/packages/n3xt0r/filament-lockbox)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/n3xt0r/filament-lockbox/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/n3xt0r/filament-lockbox/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/n3xt0r/filament-lockbox/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/n3xt0r/filament-lockbox/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/n3xt0r/filament-lockbox.svg?style=flat-square)](https://packagist.org/packages/n3xt0r/filament-lockbox)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require n3xt0r/filament-lockbox
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-lockbox-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-lockbox-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-lockbox-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentLockbox = new N3XT0R\FilamentLockbox();
echo $filamentLockbox->echoPhrase('Hello, N3XT0R!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ilya Beliaev](https://github.com/N3XT0R)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
