<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CleanupDuplicateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:cleanup-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate products and add product_code to existing products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting duplicate product cleanup...');
        
        // First, add product_code column temporarily if it doesn't exist
        if (!DB::getSchemaBuilder()->hasColumn('products', 'product_code')) {
            DB::statement('ALTER TABLE products ADD COLUMN product_code VARCHAR(255)');
            $this->info('Added temporary product_code column');
        }
        
        // Get all products with duplicate names in the same organization
        $duplicateGroups = DB::table('products')
            ->select('organization_id', 'name', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('organization_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $totalDuplicatesRemoved = 0;

        foreach ($duplicateGroups as $group) {
            $this->line("Found {$group->count} duplicates for '{$group->name}' in organization {$group->organization_id}");
            
            // Keep the first one (oldest), delete the rest
            $duplicateIds = DB::table('products')
                ->where('organization_id', $group->organization_id)
                ->where('name', $group->name)
                ->where('id', '>', $group->keep_id)
                ->pluck('id');
            
            if ($duplicateIds->count() > 0) {
                $this->line("  Deleting duplicate IDs: " . $duplicateIds->implode(', '));
                
                // Delete related price plans first
                DB::table('price_plans')->whereIn('product_id', $duplicateIds)->delete();
                
                // Delete the duplicate products
                $deleted = DB::table('products')->whereIn('id', $duplicateIds)->delete();
                $totalDuplicatesRemoved += $deleted;
                
                $this->line("  Deleted {$deleted} duplicate products");
            }
        }

        // Add product_code to existing products (generate from name)
        $productsWithoutCode = DB::table('products')
            ->where(function($query) {
                $query->whereNull('product_code')
                      ->orWhere('product_code', '');
            })
            ->get();

        $this->line("Adding product codes to {$productsWithoutCode->count()} products...");

        foreach ($productsWithoutCode as $product) {
            $baseCode = Str::slug($product->name);
            $productCode = $baseCode;
            $counter = 1;
            
            // Ensure uniqueness within organization
            while (DB::table('products')
                ->where('organization_id', $product->organization_id)
                ->where('product_code', $productCode)
                ->where('id', '!=', $product->id)
                ->exists()) {
                $productCode = $baseCode . '-' . $counter;
                $counter++;
            }
            
            DB::table('products')
                ->where('id', $product->id)
                ->update(['product_code' => $productCode]);
            
            $this->line("  Set product_code '{$productCode}' for product ID {$product->id} ('{$product->name}')");
        }

        $this->info("Cleanup completed!");
        $this->info("- Removed {$totalDuplicatesRemoved} duplicate products");
        $this->info("- Added product codes to {$productsWithoutCode->count()} products");
        
        return 0;
    }
}
