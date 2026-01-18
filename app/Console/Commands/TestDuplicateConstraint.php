<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Database\UniqueConstraintViolationException;

class TestDuplicateConstraint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:duplicate-constraint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test duplicate constraint handling for products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing duplicate product constraint handling...');
        
        try {
            $product = Product::create([
                'organization_id' => 1,
                'product_type_id' => 2,
                'name' => 'Enterprise Analytics Platform',
                'description' => 'Test duplicate product creation',
                'active' => true
            ]);
            
            $this->error('ERROR: Product created when it should have been blocked!');
            $this->line('Product ID: ' . $product->id);
            return 1;
            
        } catch (UniqueConstraintViolationException $e) {
            $this->info('SUCCESS: UniqueConstraintViolationException caught!');
            $this->line('Exception message: ' . $e->getMessage());
            
            // Test which constraint was violated
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'products_org_name_unique')) {
                $this->info('Constraint violated: products_org_name_unique (product name duplicate)');
            } elseif (str_contains($errorMessage, 'products_org_code_unique')) {
                $this->info('Constraint violated: products_org_code_unique (product code duplicate)');
            } else {
                $this->warn('Other unique constraint violated');
            }
            
            $this->info('Constraint handling is working correctly!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('OTHER EXCEPTION: ' . get_class($e));
            $this->error('Message: ' . $e->getMessage());
            return 1;
        }
    }
}
