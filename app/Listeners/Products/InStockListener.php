<?php

namespace App\Listeners\Products;

use App\Events\Products\InStockEvent;
use App\Models\User;
use App\Notifications\Products\InStockNotification;
use Illuminate\Support\Facades\Log;

class InStockListener
{
    /**
     * Create the event listener.
     */
    public function __construct(){}

    /**
     * Handle the event.
     */
    public function handle(InStockEvent $event): void
    {
        Log::info('InStockListener: ' . json_encode([
                'productId' => $event->productId,
                'message' => $event->message,
            ]));
        /*
        $users = User::all();

        foreach ($users as $user) {
            $user->notify(new InStockNotification($event->productId, $event->message));
        }
        */
    }

}
