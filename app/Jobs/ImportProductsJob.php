<?php

namespace App\Jobs;

use App\Services\ProductService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Constructor for ImportProductsJob.
     *
     * @param array $data
     * @param array $header
     * @param ProductService $productImportService
     */
    public function __construct(
        protected array          $data,
        protected array          $header,
        protected ProductService $productImportService
    )
    {
    }

    /**
     * Process each row of CSV data to update or create products and variations.
     */
    public function handle(): void
    {
        // Call the import method on the productImportService to import product data.
        // The method is provided with the data array ($this->data) containing product information
        // and the header array ($this->header) specifying the structure of the imported data.
        $this->productImportService->import($this->data, $this->header);
    }

}
