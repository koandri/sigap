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

        //add System user
        $systemUser = new User;
        $systemUser->name = 'System';
        $systemUser->email = env('SYSTEM_USER_EMAIL', 'no-reply@example.com');
        $systemUser->password = Hash::make(env('SYSTEM_USER_PASSWORD', 'password'));
        $systemUser->active = 0;
        $systemUser->save();

        //add Super Admin user
        $superAdminUser = new User;
        $superAdminUser->name = env('SUPER_ADMIN_NAME', 'Super Admin');
        $superAdminUser->email = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $superAdminUser->password = Hash::make(env('SUPER_ADMIN_PASSWORD', 'password'));
        $superAdminUser->mobilephone_no = '62811337678';
        $superAdminUser->locations = ["GL","TA","BL","GM"];
        $superAdminUser->save();

        //assign Super Admin role
        $superAdminUser->assignRole($superadmin);
    }
}
