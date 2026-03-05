<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:company,provider',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'phone' => $request->phone,
        ]);

        if ($request->role === 'company') {
            Company::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name ?? $request->name,
            ]);
        } elseif ($request->role === 'provider') {
            Provider::create([
                'user_id' => $user->id,
                'business_name' => $request->business_name ?? $request->name,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('company', 'provider'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('company', 'provider'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('company', 'provider'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'avatar' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['name', 'phone', 'avatar']));

        if ($user->role === 'company' && $user->company) {
            $request->validate([
                'company_name' => 'sometimes|string|max:255',
                'company_address' => 'nullable|string|max:255',
                'company_city' => 'nullable|string|max:100',
                'company_country' => 'nullable|string|max:100',
                'company_zipcode' => 'nullable|string|max:20',
                'company_phone' => 'nullable|string|max:20',
                'company_website' => 'nullable|string|max:255',
                'company_tax_id' => 'nullable|string|max:50',
            ]);

            $user->company->update($request->only([
                'company_name', 'company_address', 'company_city',
                'company_country', 'company_zipcode', 'company_phone',
                'company_website', 'company_tax_id'
            ]));
        }

        if ($user->role === 'provider' && $user->provider) {
            $request->validate([
                'business_name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'specialty' => 'nullable|string|max:100',
                'service_categories' => 'nullable|array',
                'service_areas' => 'nullable|array',
                'license_number' => 'nullable|string|max:50',
                'license_expiry' => 'nullable|date',
                'hourly_rate' => 'nullable|numeric|min:0',
            ]);

            $user->provider->update($request->only([
                'business_name', 'description', 'specialty',
                'service_categories', 'service_areas',
                'license_number', 'license_expiry', 'hourly_rate'
            ]));
        }

        return response()->json([
            'user' => $user->load('company', 'provider'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent'])
            : response()->json(['message' => 'Unable to send reset link'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully'])
            : response()->json(['message' => 'Invalid token'], 400);
    }
}
