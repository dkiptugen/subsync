<?php

namespace Tests\Feature;

use Tests\TestCase;

class SaasLandingTest extends TestCase
{
    public function test_landing_page_renders_the_backend_plan_catalog(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('SubSync')
            ->assertSee('Plans powered by the backend catalog.')
            ->assertSee('Choose Scale');
    }
}
