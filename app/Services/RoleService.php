<?php

namespace App\Services;

use App\Models\User;

class RoleService
{
    /**
     * Determina si un usuario puede ver la lista de todos los usuarios
     */
    public function canViewAllUsers(User $user): bool
    {
        return $user->hasRole('admin');
    }
    
    /**
     * Determina si un usuario puede ver el perfil de otro usuario
     */
    public function canViewUserProfile(User $currentUser, int $targetUserId): bool
    {
        return $currentUser->id == $targetUserId || $currentUser->hasRole('admin');
    }
    
    /**
     * Determina si un usuario puede actualizar el perfil de otro usuario
     */
    public function canUpdateUserProfile(User $currentUser, int $targetUserId): bool
    {
        return $currentUser->id == $targetUserId || $currentUser->hasRole('admin');
    }
    
    /**
     * Determina si un usuario puede eliminar a otro usuario
     */
    public function canDeleteUser(User $currentUser, int $targetUserId): bool
    {
        return $currentUser->id == $targetUserId || $currentUser->hasRole('admin');
    }
}