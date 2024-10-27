<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public array $processedProductIds = [];

    public function __construct(protected ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * Returns the CSV column mapping for product attributes.
     *
     * @return array
     */
    public function getMapping(): array
    {
        return [
            'id' => 0,
            'name' => 1,
            'sku' => 2,
            'price' => 3,
            'currency' => 4,
            'variations' => 5,
            'quantity' => 6,
            'status' => 7,
        ];
    }

    /**
     * Imports products and their variations from an array of data.
     *
     * This method processes each row in the given data array, validates and prepares the product data,
     * creates or updates the product, and updates its variations if any. Logs errors and information
     * regarding the process and soft deletes outdated products not present in the import.
     *
     * @param array $data Array of product data rows to be imported.
     * @param array $header Array representing the header which maps to the product data fields.
     *
     * @return void
     */
    public function import(array $data, array $header): void
    {
        $existingProductIds = $this->productRepository->getAllProductIds();

        foreach ($data as $productRow) {
            try {
                list($productData, $variations) = $this->validateAndPrepareProductData($productRow, $header);

                if (!$productData) {
                    Log::error("Invalid product row: " . json_encode($productRow));
                    continue;
                }

                $product = $this->productRepository->updateOrCreateProduct($productData);
                $this->processedProductIds[] = $product->id;

                Log::info("Product updated/created: " . $product);

                if (!empty($variations) && is_array($variations)) {
                    $this->updateProductVariations($product->id, $variations);
                }
            } catch (\Exception $e) {
                Log::error("Error processing product row: " . $e->getMessage() . ' - ' . json_encode($productRow));
            }
        }

        $this->softDeleteOutdatedProducts($existingProductIds);
    }

    /**
     * Soft deletes products that are not present in the processed product list.
     *
     * @param array $existingProductIds An array of existing product IDs to check against processed product IDs.
     * @return void
     */
    private function softDeleteOutdatedProducts(array $existingProductIds): void
    {
        $outdatedProductIds = array_diff($existingProductIds, $this->processedProductIds);

        if (empty($outdatedProductIds)) {
            return;
        }

        $this->productRepository->deleteOutdatedProducts($outdatedProductIds, 'deleted', 'Deleted due to synchronization');
        Log::info("Product soft deleted due to synchronization: " . json_encode($outdatedProductIds));
    }

    /**
     * Validates and prepares product data from a CSV row.
     *
     * @param array $productRow The product data row from the CSV file.
     * @param array $header An associative array mapping CSV column headers to their indices.
     * @return array|null Processed product data and variations if validation passes, null otherwise.
     */
    public function validateAndPrepareProductData(array $productRow, array $header): ?array
    {
        // Extract and validate individual fields from the product row array
        $id = $productRow[$header['id']];
        $name = $productRow[$header['name']] ?? null;
        $sku = !empty($productRow[$header['sku']]) ? $productRow[$header['sku']] : null;
        $price = $productRow[$header['price']] ?? 0;
        $currency = $productRow[$header['currency']] ?? 'SAR';
        $quantity = $productRow[$header['quantity']] ?? 0;
        $status = $productRow[$header['status']] ?? 'none';


        Log::warning("sku : " . $this->validateAndSanitizeString($sku));

        $productData = [
            'id' => $this->validateAndSanitizeString($id),
            'name' => $this->validateAndSanitizeString($name),
            'sku' => $this->validateAndSanitizeString($sku),
            'price' => $this->validatePrice($price),
            'currency' => $this->validateAndSanitizeString($currency),
            'quantity' => $this->validateInteger($quantity),
            'status' => $this->validateAndSanitizeString($status),
        ];

        if (!empty($productRow[$header['variations']])) {
            $variations = $this->sanitizeAndDecodeVariations($productRow[$header['variations']]);
        }

        return [$productData, $variations ?? null];
    }

    /**
     * Decodes a JSON string of variations and sanitizes each variation's name and value.
     *
     * @param string $variationsJson The JSON string to be decoded and sanitized.
     * @return array An array of sanitized variations. Each variation will have its 'name' and 'value' fields sanitized.
     */
    public function sanitizeAndDecodeVariations(string $variationsJson): array
    {
        $variationsJson = str_replace('""', '"', $variationsJson);  // Fix double-quoted JSON fields

        $variations = json_decode($variationsJson, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            Log::error("Invalid JSON format in row: " . $variations);
            return [];
        }

        foreach ($variations as $variation) {
            $variation['name'] = $this->validateAndSanitizeString($variation['name'] ?? '');
            $variation['value'] = $this->validateAndSanitizeString($variation['value'] ?? '');
        }

        return $variations;
    }

    /**
     * Validates and converts a given price to a float if it is numeric and greater than zero.
     *
     * @param mixed $price The price to be validated and converted.
     * @return float The validated price as a float.
     */
    public function validatePrice(mixed $price): float
    {
        return is_numeric($price) && $price > 0 ? (float)$price : 0.0;
    }

    /**
     * Validates whether the provided string is a non-negative integer.
     *
     * @param string|null $value The value to be validated.
     * @return int The validated integer, or 0 if invalid.
     */
    public function validateInteger(?string $value): int
    {
        return is_numeric($value) && $value >= 0 ? (int)$value : 0; // Change to allow 0 as valid
    }

    /**
     * Validates and sanitizes a string input.
     *
     * This method checks if the input string is empty. If it is, it returns null.
     * Otherwise, it trims any whitespace from the input and converts special characters
     * to HTML entities to ensure the input is safe for further processing or display.
     *
     * @param mixed $input The input string to validate and sanitize.
     *
     * @return string|null Returns the sanitized string, or null if the input is empty.
     */
    public function validateAndSanitizeString(mixed $input): ?string
    {
        if (empty($input)) return null;
        return htmlspecialchars(trim($input));
    }

    /**
     * Updates the variations for a given product.
     *
     * This method first deletes any existing variations associated with the specified product ID,
     * then creates new variations based on the provided array.
     *
     * @param int $productId The ID of the product whose variations are being updated.
     * @param array $variations An array of variation data, each containing 'name' and 'value'.
     * @return void
     */
    public function updateProductVariations(int $productId, array $variations): void
    {
        // Delete all existing variations for the specified product ID.
        $this->productRepository->deleteProductVariations($productId);

        // Iterate through each variation in the provided array.
        foreach ($variations as $variation) {
            // Create a new product variation using the data from the current variation.
            $this->productRepository->createProductVariation([
                'product_id' => $productId, // Associate the variation with the product ID.
                'name' => $variation['name'], // Set the name of the variation.
                'value' => $variation['value'], // Set the value of the variation.
            ]);
        }
    }

}
