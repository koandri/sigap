<?php

namespace App\Helpers;

class AuthHelper
{
    /**
     * Check if user has administrative access
     */
    public static function hasAdminAccess($user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }
        
        return $user->hasAnyRole(['super_admin', 'owner', 'business_owner']);
    }
    
    /**
     * Check if user can manage forms
     */
    public static function canManageForms($user = null)
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }
        
        return $user->hasAnyRole(['super_admin', 'owner', 'business_owner', 'manager']);
    }
}