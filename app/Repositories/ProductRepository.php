<?php

namespace App\Repositories;

use App\Events\Products\InStockEvent;
use App\Events\Products\QuantityUpdatedEvent;
use App\Models\Product;
use App\Models\Variation;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Creates a new product or updates an existing product with the provided data.
     *
     * @param array $data Associative array containing product data.
     *
     * @return Product The updated or newly created product instance.
     */
    public function updateOrCreateProduct(array $data): Product
    {
        // Attempt to find an existing product by its ID
        $product = Product::find($data['id']);

        if ($product) {
            // Store the old quantity and status before updating
            $oldQuantity = $product->quantity;
            $oldStatus = $product->status;

            // Update the product with the new data
            $product->update($data);

            // Check if the product status changed from 'out' to 'sale'
            if ($oldStatus == 'out' && $product->status == 'sale') {
                // Trigger an event to notify that the product is back in stock
                event(new InStockEvent($product->id, "This product is in stock now with " . $product->quantity . " units"));
            }

            // Check if the quantity has increased
            if ($product->quantity > $oldQuantity) {
                // Trigger an event to notify that the quantity has been updated
                event(new QuantityUpdatedEvent($product->id, $product->quantity));
            }

        } else {
            // If the product does not exist, create a new product with the provided data
            $product = Product::create($data);
        }

        // Return the product instance (either updated or newly created)
        return $product;
    }


    /**
     * Deletes all variations associated with a specified product.
     *
     * @param int $productId The ID of the product whose variations are to be deleted.
     */
    public function deleteProductVariations(int $productId): void
    {
        Variation::where('product_id', $productId)->delete();
    }

    /**
     * Creates a new product variation using the provided data.
     *
     * @param array $variationData The data for the new product variation.
     */
    public function createProductVariation(array $variationData): void
    {
        Variation::create($variationData);
    }

    /**
     * Retrieve all product IDs from the products table.
     *
     * @return array The array of product IDs.
     */
    public function getAllProductIds(): array
    {
        return Product::pluck('id')->toArray();
    }

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
    public function deleteOutdatedProducts(array $productsIds, string $status, string $hint): void
    {
        // Updates the status and hint for the specified products using their IDs
        Product::whereIn('id', $productsIds)->update(['status' => $status, 'hint' => $hint]);

        // Soft deletes the outdated products from the database using their IDs
        // Soft deletion allows us to retain the data in the database while marking it as deleted,
        // meaning we can still recover this data later if needed.
        Product::whereIn('id', $productsIds)->delete();
    }
}
