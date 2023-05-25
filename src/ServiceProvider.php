<?php

namespace JustBetter\MagentoProducts;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JustBetter\MagentoProducts\Actions\CheckAllKnownProducts;
use JustBetter\MagentoProducts\Actions\CheckKnownProducts;
use JustBetter\MagentoProducts\Actions\CheckMagentoEnabled;
use JustBetter\MagentoProducts\Actions\CheckMagentoExistence;
use JustBetter\MagentoProducts\Actions\ProcessMagentoSkus;
use JustBetter\MagentoProducts\Actions\RetrieveMagentoSkus;
use JustBetter\MagentoProducts\Actions\RetrieveProductData;
use JustBetter\MagentoProducts\Commands\CheckKnownProductsExistenceCommand;
use JustBetter\MagentoProducts\Commands\DiscoverMagentoProductsCommand;
use JustBetter\MagentoProducts\Commands\RetrieveProductDataCommand;
use JustBetter\MagentoProducts\Events\ProductDiscoveredEvent;
use JustBetter\MagentoProducts\Listeners\RegisterProduct;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magento-products.php', 'magento-products');

        CheckKnownProducts::bind();
        CheckAllKnownProducts::bind();
        CheckMagentoExistence::bind();
        CheckMagentoEnabled::bind();
        ProcessMagentoSkus::bind();
        RetrieveMagentoSkus::bind();
        RetrieveProductData::bind();
    }

    public function boot(): void
    {
        $this
            ->bootConfig()
            ->bootMigrations()
            ->bootCommands()
            ->bootEvents();
    }

    protected function bootConfig(): static
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/magento-products.php' => config_path('magento-products.php'),
            ], 'config');
        }

        return $this;
    }

    protected function bootCommands(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckKnownProductsExistenceCommand::class,
                DiscoverMagentoProductsCommand::class,
                RetrieveProductDataCommand::class,
            ]);
        }

        return $this;
    }

    protected function bootMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }

    protected function bootEvents(): static
    {
        Event::listen(ProductDiscoveredEvent::class, RegisterProduct::class);

        return $this;
    }
}
