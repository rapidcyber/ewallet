<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchants = Merchant::with('warehouses')->get();

        foreach ($merchants as $merchant) {
            $warehouses = $merchant->warehouses;

            $products = Product::factory()->count(5)->has(ProductDetail::factory())->create([
                'merchant_id' => $merchant->id
            ]);

            foreach ($products as $product) {
                if ($warehouses->count() > 1) {
                    $totalStock = $product->stock_count - 1;
                    $warehouseCount = $warehouses->count();
        
                    // Randomly split the stock count across warehouses
                    $stocks = $this->splitStocks($totalStock, $warehouseCount);
        
                    // Shuffle warehouses and assign stocks
                    $warehouses = $warehouses->shuffle();
                    foreach ($warehouses as $index => $warehouse) {
                        $product->warehouses()->attach($warehouse->id, ['stocks' => $stocks[$index]]);
                    }
                } else {
                    $product->warehouses()->attach($warehouses->first()->id, ['stocks' => $product->stock_count]);
                }

                $product->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('product_images');
            }
        }
    }

    /**
     * Function to split total stock count randomly across warehouses
     *
     * @param int $totalStock
     * @param int $warehouseCount
     * @return array
     */
    private function splitStocks(int $totalStock, int $warehouseCount): array
    {
        $stocks = [];
        $remainingStock = $totalStock;

        for ($i = 1; $i < $warehouseCount; $i++) {
            $split = fake()->numberBetween(1, $remainingStock - ($warehouseCount - $i));
            $stocks[] = $split;
            $remainingStock -= $split;
        }

        // Add the remaining stock to the last warehouse
        $stocks[] = $remainingStock;

        return $stocks;
    }
}
