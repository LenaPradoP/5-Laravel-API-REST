<?php

namespace Tests\Feature\API\User;

use Tests\Feature\API\ApiTestCase;

class DeleteUserProfileTest extends ApiTestCase
{
    /**
     * Test an authenticated user can delete their account
     */
    public function test_authenticated_user_can_delete_account(): void
    {
        $this->createAuthenticatedUser();
        $userId = $this->user->id;
        
        $response = $this->deleteJson('/api/users', [], $this->authHeaders());

        $response->assertStatus(204);
        
        // Verify user was deleted
        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    /**
     * Test all tokens are revoked when user deletes account
     */
    public function test_all_tokens_are_revoked_when_account_is_deleted(): void
    {
        $this->createAuthenticatedUser();
        $userId = $this->user->id;
        
        // Create multiple tokens for user
        $this->user->createToken('test_token_1')->accessToken;
        $this->user->createToken('test_token_2')->accessToken;
        
        // Verify tokens exist
        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $userId,
            'revoked' => 0
        ]);
        
        // Delete account
        $response = $this->deleteJson('/api/users', [], $this->authHeaders());
        $response->assertStatus(204);
        
        // Verify all tokens were revoked or deleted
        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $userId,
            'revoked' => 0
        ]);
    }

    /**
     * Test unauthenticated user cannot delete account
     */
    public function test_unauthenticated_user_cannot_delete_account(): void
    {
        $response = $this->deleteJson('/api/users');
        
        $response->assertStatus(401);
    }
}
