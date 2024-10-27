<?php

namespace App\Providers;

use App\Events\Products\InStockEvent;
use App\Events\Products\QuantityUpdatedEvent;
use App\Listeners\Products\InStockListener;
use App\Listeners\Products\QuantityUpdatedListener;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bind the ProductRepositoryInterface to its concrete implementation, ProductRepository.
        // This allows for dependency injection, enabling easy swapping of implementations.
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        // Register event listeners for specific events in the application.

        // Listen for the QuantityUpdatedEvent and associate it with the QuantityUpdatedListener.
        // This will trigger the listener whenever the event is fired, allowing for appropriate actions to be taken.
        Event::listen(
            QuantityUpdatedEvent::class,
            QuantityUpdatedListener::class,
        );

        // Listen for the InStockEvent and associate it with the InStockListener.
        // Similar to the previous event, this listener will handle actions when the product is marked as back in stock.
        Event::listen(
            InStockEvent::class,
            InStockListener::class,
        );

    }
}
