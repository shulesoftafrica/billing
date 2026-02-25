<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class ShowProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all products with their codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::select('id', 'name', 'product_code', 'organization_id')->get();
        
        $this->info('Existing products:');
        
        $headers = ['ID', 'Name', 'Product Code', 'Org ID'];
        $rows = [];
        
        foreach ($products as $product) {
            $rows[] = [
                $product->id,
                $product->name,
                $product->product_code ?? 'NULL',
                $product->organization_id
            ];
        }
        
        $this->table($headers, $rows);
        
        return 0;
    }
}
