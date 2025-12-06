[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

A pure PHP implementation of Shamir's Secret Sharing scheme with fluent conductor API, allowing secrets to be split into N shares where any M shares can reconstruct the original secret. Features zero external dependencies, information-theoretic security, and automatic chunking for large secrets.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/shamir
```

## Documentation

Full documentation is available at [https://docs.cline.sh/shamir](https://docs.cline.sh/shamir):

- **[Getting Started](https://docs.cline.sh/shamir/getting-started)** - Installation, basic usage, and quick start
- **[API Reference](https://docs.cline.sh/shamir/api-reference)** - Complete API documentation
- **[Use Cases](https://docs.cline.sh/shamir/use-cases)** - Real-world examples and practical applications
- **[Security](https://docs.cline.sh/shamir/security)** - Security considerations and best practices
- **[Advanced Usage](https://docs.cline.sh/shamir/advanced-usage)** - Advanced patterns and techniques

## Quick Example

```php
use Cline\Shamir\Shamir;

// Split a secret into 5 shares, requiring 3 to reconstruct
$shares = Shamir::for('my-encryption-key')
    ->threshold(3)
    ->shares(5)
    ->split();

// Reconstruct from any 3 shares
$secret = Shamir::from($shares->take(3))->combine();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/shamir/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/shamir.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/shamir.svg

[link-tests]: https://github.com/faustbrian/shamir/actions
[link-packagist]: https://packagist.org/packages/cline/shamir
[link-downloads]: https://packagist.org/packages/cline/shamir
[link-security]: https://github.com/faustbrian/shamir/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
