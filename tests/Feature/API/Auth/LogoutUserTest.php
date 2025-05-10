<?php

namespace Tests\Feature\API\Auth;

use Tests\Feature\API\ApiTestCase;

class LogoutUserTest extends ApiTestCase
{
    /**
     * Test successful logout
     */
    public function test_user_can_logout_successfully(): void
    {
        $this->createAuthenticatedUser();
        
        $response = $this->deleteJson('/api/tokens', [], $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    /**
     * Test unauthorized logout attempt
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->deleteJson('/api/tokens');

        $response->assertStatus(401);
    }
}