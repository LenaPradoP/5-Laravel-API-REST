<?php

namespace App\Observers;

use App\Models\User;
use App\Services\DeckService;

class UserObserver
{
    protected $deckService;

    public function __construct(DeckService $deckService)
    {
        $this->deckService = $deckService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->deckService->createDeckForUser($user);
    }
}
