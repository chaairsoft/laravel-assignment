<?php

namespace App\Console\Commands;

use App\Services\ProductSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync products from external API to local database';

    public function __construct(protected ProductSyncService $productSyncService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting product synchronization...');
        $this->productSyncService->syncProducts();
        $this->info('Product synchronization completed.');
    }
}
