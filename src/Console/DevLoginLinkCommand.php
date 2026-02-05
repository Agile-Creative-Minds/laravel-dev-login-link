<?php

namespace AgileCreativeMinds\DevLoginLink\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Artisan command to generate one-time login links for users.
 *
 * This command creates temporary signed URLs that allow developers
 * to quickly log in as any user during local development without
 * needing to know their password.
 *
 * Features:
 * - Auto-creates a default admin user if the database is empty
 * - Generates signed URLs that expire after 10 minutes
 * - Only runs in local/development/testing environments
 */
class DevLoginLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:login-link {userId? : ID of the user to generate a link for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a one-time login link for a user (dev only)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            $this->error('This command is only available in local/development environments.');

            return Command::FAILURE;
        }

        $userModel = $this->getUserModel();
        $userId = $this->argument('userId');

        // If specific userId requested, try to find that user
        if ($userId) {
            $user = $userModel::find($userId);

            if (! $user) {
                $this->error("User with ID {$userId} not found.");

                return Command::FAILURE;
            }
        } elseif ($userModel::count() === 0) {
            // No users exist and no specific user requested - create default admin
            $user = $this->createDefaultAdmin($userModel);
        } else {
            // Use first available user
            $user = $userModel::first();
        }

        $url = URL::temporarySignedRoute(
            'dev.login',
            now()->addMinutes(10),
            ['user' => $user->id]
        );

        $this->newLine();
        $this->info('One-time login link (expires in 10 minutes):');
        $this->newLine();
        $this->line($url);
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Get the user model class.
     */
    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', 'App\\Models\\User');
    }

    /**
     * Create a default admin user when the users table is empty.
     */
    protected function createDefaultAdmin(string $userModel): mixed
    {
        $password = Str::random(12);

        $user = $userModel::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make($password),
        ]);

        $this->warn('No users found. Created default admin user:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $user->email],
                ['Password', $password],
            ]
        );
        $this->newLine();

        return $user;
    }
}
