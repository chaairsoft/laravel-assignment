<?php

namespace App\Repositories\Contracts;

use App\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Updates an existing product or creates a new one if it doesn't exist.
     *
     * This method checks if a product with the specified data already exists.
     * If it does, it updates the product with the provided data.
     * If not, it creates a new product with the specified details.
     *
     * @param array $data An associative array containing product details.
     *
     * @return Product The updated or newly created Product instance.
     */
    public function updateOrCreateProduct(array $data): Product;

    /**
     * Deletes all variations associated with a specific product.
     *
     * This method removes all variations linked to the provided product ID.
     * It is useful for cleaning up variations before making significant changes
     * to the product or when the product itself is being deleted.
     *
     * @param int $productId The ID of the product whose variations will be deleted.
     *
     * @return void
     */
    public function deleteProductVariations(int $productId): void;

    /**
     * Creates a new variation for a product.
     *
     * This method takes an associative array of variation data and creates a
     * new variation associated with a product. Variation data may include
     * details such as color, size, or any other attributes that differentiate
     * this variation from the main product.
     *
     * @param array $variationData An associative array containing variation details.
     *
     * @return void
     */
    public function createProductVariation(array $variationData): void;


    /**
     * Retrieves all product IDs from the database.
     *
     * This method queries the database to retrieve a list of all product IDs.
     * It is useful for operations that need to process all products or check
     * the existence of specific products by their IDs.
     *
     * @return array An array of product IDs (integers).
     */
    public function getAllProductIds(): array;

    /**
     * Deletes outdated products from the database.
     *
     * This method marks specified products as outdated by updating their status and hint before performing a soft delete.
     * It helps keep the product catalog clean and relevant by removing entries that are no longer useful.
     *
     * @param array $productsIds An array of product IDs (integers) to be marked for deletion.
     * @param string $status The new status to assign to the outdated products.
     * @param string $hint A hint message providing context for the status change.
     * @return void
     */
    public function deleteOutdatedProducts(array $productsIds, string $status, string $hint): void;

}
