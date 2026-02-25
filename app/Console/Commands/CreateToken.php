<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:create {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new API token for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }
        
        $token = $user->createToken('test-token-' . now()->format('Y-m-d-H-i-s'));
        
        $this->info("New token created for user {$user->email}:");
        $this->line($token->plainTextToken);
        
        return 0;
    }
}
