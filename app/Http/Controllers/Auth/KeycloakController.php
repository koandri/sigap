<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Services\PushoverService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;

class KeycloakController extends Controller
{
    public function __construct(
        private readonly PushoverService $pushoverService
    ) {}
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

            dd($user);

            // If user doesn't exist, create a new user
            if (!$user) {
                $user = new User();
                $user->name = $keycloakUser->getName();
                $user->email = $keycloakUser->getEmail();
                $user->password = Hash::make(Str::random(20));
                $user->active = true;
                $user->save();
            }

            if (!$user->active) {
                return redirect()->route('login')
                    ->withErrors([
                        'error' => 'Your account is not active. Please contact the IT Staff.'
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
            // Log the actual error for debugging
            Log::error('Keycloak SSO Error', [
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            $this->pushoverService->sendWhatsAppFailureNotification(
                'SIGaP SSO Login Error',
                env('WAHA_DEFAULT_CHAT_ID'),
                "SSO login error from IP: " . request()->ip() . ". Error: " . $e->getMessage()
            );

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