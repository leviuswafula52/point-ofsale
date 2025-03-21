<?php

namespace App\Http\Requests\Auth;

use App\Enums\ShopType;
use App\Rules\Domain;
use App\Services\RegisterTenant;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function __construct(public RegisterTenant $registerTenant)
    {
        parent::__construct(); 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $domainParts = explode('.', $this->domain);
        if (count($domainParts) > 2) {
            $this->merge([
                'name' => strtolower($domainParts[0]),
                'domain' => strtolower($this->domain),
            ]);
        } else {
            $this->merge([
                'name' => strtolower($this->domain),
                'domain' => strtolower($this->domain) . '.' . config('tenancy.central_domains')[0],
            ]);
        }

        return [
            'domain' => [
                'required', 'string', 'max:255',
                Rule::unique('domains', 'domain'), 
                new Domain,
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('tenant_users', 'email'), 
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_type' => ['required', Rule::in(ShopType::cases())],
            'other_business_type' => ['required_if:business_type,other'],
        ];
    }

    /**
     * Register a new tenant.
     *
     * @return Tenant
     * @throws ValidationException
     */
    public function register(): Tenant
    {
        try {
            return $this->registerTenant->create($this->validated());
        } catch (ValidationException $e) {
            throw ValidationException::withMessages([
                'error' => 'Tenant registration failed: ' . $e->getMessage(),
            ]);
        }
    }
}
