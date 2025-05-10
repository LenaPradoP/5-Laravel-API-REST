<?php

namespace App\Services;

use App\Models\User;
use App\Models\Spread;

class RoleService
{

    public function canViewAllUsers(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function canViewUserProfile(User $currentUser, int $targetUserId): bool
    {
        return $currentUser->id == $targetUserId || $currentUser->hasRole('admin');
    }
    
    public function canUpdateUserProfile(User $currentUser, int $targetUserId): bool
    {
        return $currentUser->id == $targetUserId || $currentUser->hasRole('admin');
    }
    
    public function canDeleteUser(User $currentUser, int $targetUserId): bool
    {
        return $currentUser->id == $targetUserId || $currentUser->hasRole('admin');
    }

        public function canViewAllSpreads(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function canViewSpread(User $user, Spread $spread): bool
    {
        return $user->hasRole('admin') || 
            ($user->deck && $spread->deck_id === $user->deck->id);
    }

    public function canDeleteSpread(User $user, Spread $spread): bool
    {
        return $this->canViewSpread($user, $spread);
    }
}