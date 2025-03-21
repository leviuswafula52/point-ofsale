<?php

namespace App\Services;

use App\Constants\Role;
use App\Models\Tenants\About;
use App\Models\Tenants\User;
use App\Notifications\DomainCreated;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Exception;

class RegisterTenant
{
    /**
     * Create a new tenant with domain and user setup.
     *
     * @param array $data
     * @return Tenant
     * @throws Exception
     */
    public function create(array $data): Tenant
    {
        try {
            $name = $data['name'] ?? null;
            if (!$name) {
                throw new Exception('Tenant name is required.');
            }

            // Create Tenant
            $tenant = Tenant::create([
                'id' => $name,
                'tenancy_db_name' => 'lakasir_' . $name,
                'tenancy_email' => $data['email'],
            ]);

            // Attach domain
            $tenant->domains()->create([
                'domain' => $data['domain'],
            ]);

            // Set up tenant environment
            $tenant->run(function () use ($data) {
                // Create Owner User
                $user = User::create([
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                    'is_owner' => true,
                ]);

                // Create About Info
                About::create([
                    'shop_name' => $data['full_name'] ?? 'Unnamed Shop',
                    'business_type' => $data['business_type'],
                    'other_business_type' => $data['other_business_type'] ?? null,
                ]);

                // Notify User
                $user->notify(new DomainCreated());

                // Seed Initial Data
                $this->seedDatabase();

                // Assign Role
                $user->assignRole(Role::admin);
            });

            return $tenant;
        } catch (Exception $e) {
            Log::error('Tenant registration failed: ' . $e->getMessage());
            throw new Exception('Tenant registration failed. Please try again.');
        }
    }

    /**
     * Seed database with necessary data.
     */
    private function seedDatabase(): void
    {
        Artisan::call('db:seed', ['--class' => 'PermissionSeeder']);
        Artisan::call('db:seed', ['--class' => 'PaymentMethodSeeder']);
        Artisan::call('db:seed', ['--class' => 'CategorySeeder']);
    }
}
