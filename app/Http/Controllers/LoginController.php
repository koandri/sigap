<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Models\User;

class LoginController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('asana')->redirect();
        //return Socialite::driver('authentik')->redirect();
    }

    public function callback()
    {
        $intendedUrl = session('intended_url');

        $socialiteUser = Socialite::driver('asana')->user();
        //$socialiteUser = Socialite::driver('authentik')->user();
    
        //get socialiteUser email
        $email = $socialiteUser->email;
    
        //only allow users with domains: ptsiap.com, suryagroup.app
        if (Str::endsWith($email, ['@ptsiap.com', '@ptsiap.id', '@suryagroup.app'])) {
            $user = User::where('active', 1)->where('asana_id', $socialiteUser->id)->first();

            if (!$user) {
                //try to check by email
                $user = User::where('active', 1)->where('email', $socialiteUser->email)->first();

                if ($user && is_null($user->asana_id)) {
                    //if found and asana_id is null, update the user
                    $user->asana_id = $socialiteUser->id;
                    $user->save();
                }
            }
            
            //if still not found, create a new user
            if (!$user) {
                $user = new User;
                $user->name = $socialiteUser->name;
                $user->email = $socialiteUser->email;
                $user->asana_id = $socialiteUser->id;
                $user->save();
            }

            //For Authentik
            //$user = User::where('email', $socialiteUser->email)->first();

            //if user not found, create a new user
            /*
            if (!$user) {
                $user = new User;
                $user->name = $socialiteUser->name;
                $user->email = $socialiteUser->email;
                $user->save();
            }
            */
    
            //only logins an active user
            if ($user->active) {
                Auth::login($user);
            }
        }
     
        return redirect()->intended($intendedUrl);
    }
}
