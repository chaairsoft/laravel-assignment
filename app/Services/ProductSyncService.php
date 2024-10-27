<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variation;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductSyncService
{
    protected string $apiUrl = 'https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products';

    /**
     * Fetches products from the external API.
     *
     * @return array An array of products or an empty array if the fetch fails.
     */
    public function fetchProducts(): array
    {
        try {
            // Fetch products without verifying SSL for development purposes
            $response = Http::withoutVerifying()->get($this->apiUrl);

            // For Production
            //$response = Http::get($this->apiUrl);

            // Check if the response was successful
            if ($response->successful()) {
                return $response->json(); // Return the JSON data as an array
            } else {
                // Log an error if the API call was unsuccessful
                Log::error('Failed to fetch products from API', ['status' => $response->status()]);
                return []; // Return an empty array on failure
            }
        } catch (Exception $e) {
            // Log any exceptions that occur during the fetch
            Log::error('Error fetching products: ' . $e->getMessage());
            return []; // Return an empty array on exception
        }
    }

    /**
     * Synchronizes products and their variations with the database.
     *
     * @return void
     */
    public function syncProducts(): void
    {
        // Fetch products from the API
        $products = $this->fetchProducts();

        // Iterate through each product and update or create it in the database
        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['id' => $productData['id']], // Find product by ID
                [
                    'name' => $productData['name'],
                    'image' => $productData['image'],
                    'price' => $productData['price'],
                    'created_at' => $productData['created_at'],
                ]
            );

            // Sync variations associated with the product
            $this->syncProductVariations($productData['variations']);
        }

        // Log the successful synchronization of products and variations
        Log::info('Products and variations synchronized successfully.');
    }

    /**
     * Sync variations for a specific product.
     *
     * @param array $variations The variations data for a product.
     * @return void
     */
    private function syncProductVariations(array $variations): void
    {
        // Iterate through each variation and update or create it in the database
        collect($variations)->each(function ($variationData) {
            foreach (['color', 'material', 'quantity', 'additional_price'] as $name) {
                if (isset($variationData[$name])) {
                    Variation::updateOrCreate(
                        [
                            'product_id' => $variationData['productId'], // Find variation by product ID
                            'name' => $name, // Variation name (color, material, etc.)
                        ],
                        [
                            'value' => $variationData[$name], // Variation value
                        ]
                    );
                }
            }
        });
    }
}
