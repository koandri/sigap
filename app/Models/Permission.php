<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission As PermissionModel;

class Permission extends PermissionModel
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'guard_name',
    ];
}
