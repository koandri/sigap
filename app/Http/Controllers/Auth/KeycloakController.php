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
            
            // Check if user exists in local database
            $user = User::where('email', $keycloakUser->getEmail())->first();
            
            // If user doesn't exist, deny access
            if (!$user) {
                return redirect()->route('login')
                    ->withErrors([
                        'error' => 'Your account is not registered in this application. Please contact the IT Staff.'
                    ]);
            }

            if (!$user->active) {
                return redirect()->route('login')
                    ->withErrors([
                        'error' => 'Your account is not not active. Please contact the IT Staff.'
                    ]);
            }
            
            //Update the user keycloak_id
            $user->update([
                'keycloak_id' => $keycloakUser->getId(),
            ]);

            // Login the user
            Auth::login($user, true);

            // Redirect to intended page or dashboard
            return redirect()->intended('/dashboard');
            
        } catch (\Exception $e) {            
            return redirect()->route('login')
                ->withErrors(['error' => 'Unable to login with SSO. Please try again or use password login.']);
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