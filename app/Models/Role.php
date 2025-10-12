<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role As RoleModel;

class Role extends RoleModel
{
    use HasFactory;
}
