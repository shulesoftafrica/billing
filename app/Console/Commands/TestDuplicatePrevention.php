<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class TestDuplicatePrevention extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:test-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test duplicate prevention for products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing duplicate prevention...');
        
        try {
            // Try to create a product with duplicate name
            $product = Product::create([
                'organization_id' => 1,
                'product_type_id' => 2,
                'name' => 'Enterprise Analytics Platform',
                'product_code' => 'enterprise-analytics-platform',
                'description' => 'This should fail due to duplicate name',
                'active' => true,
            ]);
            
            $this->error('ERROR: Duplicate creation should have failed!');
            
        } catch (\Exception $e) {
            $this->info('SUCCESS: Duplicate name prevented - ' . $e->getMessage());
        }

        try {
            // Try to create a product with duplicate product_code
            $product = Product::create([
                'organization_id' => 1,
                'product_type_id' => 2,
                'name' => 'Different Name',
                'product_code' => 'enterprise-analytics-platform',
                'description' => 'This should fail due to duplicate product_code',
                'active' => true,
            ]);
            
            $this->error('ERROR: Duplicate product_code should have failed!');
            
        } catch (\Exception $e) {
            $this->info('SUCCESS: Duplicate product_code prevented - ' . $e->getMessage());
        }

        try {
            // Try to create a valid new product
            $product = Product::create([
                'organization_id' => 1,
                'product_type_id' => 2,
                'name' => 'New Unique Product',
                'product_code' => 'new-unique-product',
                'description' => 'This should work - completely unique',
                'active' => true,
            ]);
            
            $this->info('SUCCESS: New unique product created with ID: ' . $product->id);
            
            // Clean up - delete the test product
            $product->delete();
            $this->line('Test product cleaned up');
            
        } catch (\Exception $e) {
            $this->error('ERROR: Valid product creation failed - ' . $e->getMessage());
        }

        // Show current products
        $products = DB::table('products')->select('id', 'organization_id', 'name', 'product_code')->get();
        $this->line("\nCurrent products:");
        $this->table(
            ['ID', 'Org ID', 'Name', 'Product Code'],
            $products->map(function($product) {
                return [$product->id, $product->organization_id, $product->name, $product->product_code];
            })->toArray()
        );

        $this->info("\nTest completed!");
        
        return 0;
    }
}
