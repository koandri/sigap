<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelOptions\Options;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;

use App\Mail\UserRegistration;

use App\Enums\Location;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(25);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $locations = Options::forEnum(Location::class);
        $managers = Options::forModels(User::class);

        $departments = Department::all();

        if (Auth::user()->hasRole('Super Admin')) {
            $roles = Role::orderBy('name')->get();

            $permissions = Permission::orderBy('name')->get();
        }
        else {
            $roles = Role::whereNotIn('name', array('Super Admin', 'Owner'))
                        ->orderBy('name')
                        ->get();

            $permissions = Permission::orderBy('name')->get();
        }

        return view('users.create', compact('locations', 'managers', 'departments', 'roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'mobilephone_no' => 'required|string|max:16|starts_with:628|unique:users,mobilephone_no',
            'manager_id' => 'nullable|integer|exists:users,id',
            'locations' => 'nullable|array',
            'departments' => 'nullable|array',
            'departments.*' => 'integer|exists:departments,id',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect('/users/create')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        //create a new random password
        $plain_password = Str::password(8);
        $hashed_password = array('password' => Hash::make($plain_password));

        $validated = array_merge($validated, $hashed_password);

        $user = User::create($validated);

        //Update User Departments
        $user->departments()->sync($request->departments);

        //Update User Roles
        $user->roles()->sync($request->roles);

        //Update User Permissions
        $user->permissions()->sync($request->permissions);

        //Send WA Notification containing Login Details to User
        $chatId = validateMobileNumber($user->mobilephone_no);

        $message = View::make('messages.user_registration', [
                            'user' => $user,
                            'plain_password' => $plain_password,
                        ])->render();

        $response = Http::acceptJson()
                            ->withHeaders([
                                'X-Api-Key' => env('WHATSAPP_API_KEY')
                            ])
                            ->post(env('WHATSAPP_WAHA_ENDPOINT') . "/api/sendText", [
                                'session' => 'ptsiap',
                                'chatId' => $chatId,
                                'linkPreview' => false,
                                'text' => $message
                            ]);

        if ($response->failed()) {
            //notify by email
            Mail::to($user->email)->send(new UserRegistration($user, $plain_password));
        }

        return redirect()->route('roles.index')->with(['success' => 'A new user created!']);
    }

    public function edit(User $user)
    {
        $managers = Options::forModels(User::class);
        $locations = Options::forEnum(Location::class);

        if ($user->hasRole('Super Admin|Owner')) {
            if (Auth::user()->hasRole('Super Admin|Owner')) {
                //allow
            }
            else {
                abort(403);
            }
        }

        $departments = Department::whereNotIn('id', $user->departments()->pluck('id'))
                                ->orderBy('name')
                                ->get();

        if (Auth::user()->hasRole('Super Admin')) {
            $roles = Role::whereNotIn('id', $user->roles()->pluck('id'))
                        ->orderBy('name')
                        ->get();
            
            $permissions = Permission::whereNotIn('id', $user->permissions()->pluck('id'))
                                    ->orderBy('name')
                                    ->get();
        }
        else {
            $roles = Role::whereNotIn('name', array(['Super Admin', 'Owner']))
                        ->whereNotIn('id', $user->roles()->pluck('id'))
                        ->orderBy('name')
                        ->get();
            
            $permissions = Permission::whereNotIn('id', $user->permissions()->pluck('id'))
                                    ->orderBy('name')
                                    ->get();
        }

        return view('users.edit', compact('user', 'managers', 'locations', 'departments', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobilephone_no' => 'required|string|max:16|starts_with:628|unique:users,mobilephone_no,'.$user->id,
            'manager_id' => 'nullable|integer|exists:users,id',
            'locations' => 'nullable|array',
            'departments' => 'nullable|array',
            'departments.*' => 'integer|exists:departments,id',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'active' => 'required|boolean',
        ])->validate();

        //Update User Details
        $user->update($validated);

        //Update User Locations
        if (!$request->has('locations')) {
            $user->locations = null;
            $user->save();
        }
        
        //Update User Departments
        $user->departments()->sync($request->departments);

        //Update User Roles
        $user->roles()->sync($request->roles);

        //Update User Permissions
        $user->permissions()->sync($request->permissions);

        return redirect()->route('users.index')->with(['success' => 'User has been updated!']);
    }

    public function show(User $user)
    {
        $permissions = Permission::orderBy('name')->get();

        return view('users.show', compact('user', 'permissions'));
    }

    public function editmyprofile(Request $request)
    {
        return view('profile.editmyprofile');
    }
}
