<?php

namespace App\Filament\Tenant\Pages;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Pages\Auth\Login;
use Illuminate\Validation\ValidationException;

class TenantLogin extends Login
{
    public function authenticate(): ?LoginResponse
    {
        $loginResponse = parent::authenticate();
        
        /** @var \App\Models\Tenants\User $user */
        $user = Filament::auth()->user();
        
        if (!$user->can('access web app')) {
            throw ValidationException::withMessages([
                'data.email' => 'You do not have permission to access the web app',
            ]);

            return null;
        }

        $user->profile()->updateOrCreate(
            [
                'user_id' => $user->getKey(),
            ],
            [
                'timezone' => 'Africa/Nairobi',
            ]
        );

        return $loginResponse;
    }

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        if (app()->environment('demo')) {
            $this->form->fill([
                'email' => 'demo@pos.com',
                'password' => '123',
            ]);
        }
    }
}