# Magento Products

<p>
    <a href="https://github.com/justbetter/laravel-magento-products"><img src="https://img.shields.io/github/actions/workflow/status/justbetter/laravel-magento-products/tests.yml?label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://github.com/justbetter/laravel-magento-products"><img src="https://img.shields.io/github/actions/workflow/status/justbetter/laravel-magento-products/analyse.yml?label=analysis&style=flat-square" alt="Analysis"></a>
    <a href="https://github.com/justbetter/laravel-magento-products"><img src="https://img.shields.io/packagist/dt/justbetter/laravel-magento-products?color=blue&style=flat-square" alt="Total downloads"></a>
</p>

This package tracks if products exist in Magento by storing the status locally in the DB.
We developed this to prevent multiple calls when multiple packages need to check product existance in Magento.
This package does do the assumption that once a product exists in Magento it will always be there.


## Installation

Require this package:

```shell
composer require justbetter/laravel-magento-products
```

Add the following to your schedule to automatically search for products in Magento.

```php
$schedule->command(\JustBetter\MagentoProducts\Commands\CheckKnownProductsExistenceCommand::class)->twiceDaily();
$schedule->command(\JustBetter\MagentoProducts\Commands\DiscoverMagentoProductsCommand::class)->daily();
```

## Usage

### Checking if a product exists in Magento

You can use this package to determine if products exist in Magento.
For example:


```php
$exists = app(\JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence::class)->exists('sku')
```

If it does not exist the sku will still be stored in the database. The `\JustBetter\MagentoProducts\Commands\CheckKnownProductsExistenceCommand` command will automatically check these known products for existence.

### Retrieving product data

You can use this package to retrieve product data. This data will be saved in the database and automatically retrieved when it is older than X hours.
You can configure the amount of ours in the config file
For example:

```php
$exists = app(\JustBetter\MagentoProducts\Contracts\RetrievesProductData::class)->retrieve('sku')
```


## Events

When your application discovers new products in Magento you should dispatch one of the following events:

`\JustBetter\MagentoProducts\Events\ProductDiscoveredEvent` containing a single sku.

When a single product or multiple products appear in Magento, an event is dispatched:

`\JustBetter\MagentoProducts\Events\ProductCreatedInMagentoEvent` containing a single sku.


## Quality

To ensure the quality of this package, run the following command:

```shell
composer quality
```

This will execute three tasks:

1. Makes sure all tests are passed
2. Checks for any issues using static code analysis
3. Checks if the code is correctly formatted

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vincent Boon](https://github.com/VincentBean)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
