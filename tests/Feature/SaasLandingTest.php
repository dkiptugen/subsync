<?php

namespace Tests\Feature;

use Tests\TestCase;

class SaasLandingTest extends TestCase
{
    public function test_removed_landing_page_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
