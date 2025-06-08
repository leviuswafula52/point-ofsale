<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

class RegisteredUserController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $tenant = $request->register();

        if ($tenant) {
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully!',
                'data' => $tenant,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Registration failed!',
        ], 400);
    }
}