<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthResolver
{
    /**
     * Login a user and return an API token.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function login($_, array $args)
    {
        $user = User::where('email', $args['email'])->first();

        if (!$user || !Hash::check($args['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => null, // Sanctum tokens don't expire by default
        ];
    }

    /**
     * Logout the authenticated user.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function logout($_, array $args)
    {
        $user = Auth::user();

        if ($user) {
            // Delete current token
            $user->currentAccessToken()->delete();
        }

        return [
            'message' => 'Successfully logged out',
            'success' => true,
        ];
    }

    /**
     * Register a new user.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function register($_, array $args)
    {
        // Create user
        $user = User::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'password' => Hash::make($args['password']),
            'role' => 'employee', // Default role
            'department' => $args['department'] ?? null,
            'job_title' => $args['job_title'] ?? null,
            'points' => 0,
            'level' => 1,
            'experience' => 0,
            'total_badges' => 0,
            'ideas_submitted' => 0,
            'ideas_approved' => 0,
            'comments_posted' => 0,
            'likes_given' => 0,
            'likes_received' => 0,
            'is_active' => true,
        ]);

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => null,
        ];
    }
}
