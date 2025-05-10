<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BasicEnvironmentTest extends TestCase
{
    /**
     * Test that the PHP environment is working correctly.
     */
    public function test_php_environment_works(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertEquals(4, 2 + 2);
        $this->assertNotEquals(5, 2 + 2);
    }
}
