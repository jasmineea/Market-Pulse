<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketpulse:super-admin
                            {email : The super admin email}
                            {--name= : The super admin name (optional, will prompt if not set)}
                            {--password= : The password (optional, will prompt if not set)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update a super admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');

            return self::FAILURE;
        }

        $role = Role::where('slug', 'super_admin')->first();

        if (! $role) {
            $this->error('Super Admin role not found. Run php artisan db:seed --class=RoleSeeder first.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Name', 'Super Admin');
        $password = $this->option('password') ?: $this->secret('Password');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role_id' => $role->id,
            ]
        );

        $this->info("Super admin created/updated for {$email}.");

        return self::SUCCESS;
    }
}
