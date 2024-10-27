<?php

namespace App\Console\Commands;

use App\Jobs\ImportProductsJob;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportProductsCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected ProductRepositoryInterface $productRepository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $csvPath = public_path("CSVFiles/products.csv");
        $chunkSize = 200;

        // Verify if the CSV file exists in the specified path
        if (!file_exists($csvPath)) {
            $this->error("CSV file does not exist at path: $csvPath");
            return;
        }

        dump("CSV processing initiated...");

        Log::info("CSV processing initiated...");

        // Read CSV file and chunk data for batch processing
        $chunks = array_chunk(file($csvPath), $chunkSize);

        try {
            // Create an instance of ProductService
            $productService = new ProductService($this->productRepository);
            // Dispatching batch jobs to handle large CSV data processing
            $batch = Bus::batch([])->dispatch();

            foreach ($chunks as $key => $chunk) {
                $data = array_map('str_getcsv', $chunk);

                // Skip the header row for the first chunk
                if ($key === 0) unset($data[0]);

                // Add the chunked data as a job to the batch
                $batch->add(new ImportProductsJob($data, $productService->getMapping(), $productService));
            }
        } catch (Exception $e) {
            // Log any errors that occur during batch processing
            Log::error("Failed to process CSV file: " . $e->getMessage());
            return;
        }
    }
}
