<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class KeycloakController extends Controller
{
    /**
     * Redirect to Keycloak login
     */
    public function redirectToKeycloak()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    /**
     * Handle callback from Keycloak
     */
    public function handleKeycloakCallback()
    {
        try {
            // Get user from Keycloak
            $keycloakUser = Socialite::driver('keycloak')->user();
            
            // Find or create user in local database
            $user = User::updateOrCreate(
                ['email' => $keycloakUser->getEmail()],
                [
                    'name' => $keycloakUser->getName() ?? $keycloakUser->getNickname(),
                    'email' => $keycloakUser->getEmail(),
                    'email_verified_at' => now(),
                    // Store Keycloak ID for reference
                    'keycloak_id' => $keycloakUser->getId(),
                ]
            );

            // Login the user
            Auth::login($user, true);

            // Redirect to intended page or dashboard
            return redirect()->intended('/dashboard');
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Keycloak SSO Error: ' . $e->getMessage());
            
            return redirect()->route('login')
                ->withErrors(['sso' => 'Unable to login with SSO. Please try again or use password login.']);
        }
    }

    /**
     * Logout from both Laravel and Keycloak
     */
    public function logout()
    {
        // Logout from Laravel
        Auth::logout();
        
        // Invalidate session
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        // Build Keycloak logout URL
        $keycloakLogoutUrl = env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALMS') . '/protocol/openid-connect/logout';
        $redirectUri = url('/');
        
        // Redirect to Keycloak logout
        return redirect($keycloakLogoutUrl . '?redirect_uri=' . urlencode($redirectUri));
    }
}