<?php

namespace App\Listeners\Products;

use App\Events\Products\QuantityUpdatedEvent;
use Illuminate\Support\Facades\Log;

class QuantityUpdatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QuantityUpdatedEvent $event): void
    {
        Log::info('QuantityUpdatedListener : ' . json_encode([
                'productId' => $event->productId,
                'newQuantity' => $event->newQuantity,
            ]));

        // Fetch the interested customers (you may need to customize this logic)
        /*
        $customers = $this->getCustomersInterestedInProduct($event->productId);

        foreach ($customers as $customer) {
            $customer->notify(new QuantityUpdatedNotification($event->productId, $event->newQuantity));
        }*/
    }

    /*
    protected function getCustomersInterestedInProduct(int $productId): array
    {
        // Example query to get customers interested in the product
        return User::where('interested_products', 'LIKE', '%' . $productId . '%')->get();
    }*/
}
