<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //add Super Admin role
        $superadmin = new Role;
        $superadmin->name = 'Super Admin';
        $superadmin->save();

        //add Owner role
        $owner = new Role;
        $owner->name = 'Owner';
        $owner->save();

        //add user
        $user = new User;
        $user->name = 'System';
        $user->email = 'no-reply@suryagroup.app';
        $user->password = Hash::make('9HBdPtURT2EK3a-yB6Nd');
        $user->active = 0;
        $user->save();

        //add Super Admin user
        $user = new User;
        $user->name = 'Andri Halim Gunawan';
        $user->email = 'andri@ptsiap.com';
        $user->password = Hash::make('Jtv6NVKZ9-ouHqvm.jwP');
        $user->save();

        //assign Super Admin role
        $user->assignRole($superadmin);
    }
}
