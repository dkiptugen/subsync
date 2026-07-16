<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityBaselineTest extends TestCase
{
    public function test_sensitive_web_tools_require_authentication(): void
    {
        $this->get('/plugins')->assertRedirect('/login');
        $this->get('/media-library')->assertRedirect('/login');
        $this->post('/media-library')->assertRedirect('/login');
    }

    public function test_local_debug_and_cache_routes_are_not_registered_while_testing(): void
    {
        $this->get('/debug')->assertNotFound();
        $this->get('/test')->assertNotFound();
        $this->get('/cache/config')->assertNotFound();
    }

    public function test_organization_password_reset_requires_api_authentication(): void
    {
        $this->postJson('/api/update-pass/1', [
            'password' => 'SecurePassword!1',
            'password_confirmation' => 'SecurePassword!1',
        ])->assertUnauthorized();
    }

    public function test_b2b_routes_have_a_single_prefix_and_require_authentication(): void
    {
        $this->assertSame('/b2b', route('client_dashboard.index', absolute: false));
        $this->get('/b2b')->assertRedirect('/login');
        $this->get('/b2b/b2b')->assertNotFound();
    }

    public function test_social_login_rejects_unsupported_providers_before_contacting_them(): void
    {
        config(['custom.APP.API_KEY' => 'test-api-key']);

        $this->withHeader('appkey', 'test-api-key')
            ->postJson('/api/auth/social_login_v2', [
                'provider' => 'unsupported',
                'access_token' => 'test-token',
            ])
            ->assertUnprocessable();
    }

    public function test_queue_connections_dispatch_after_commit(): void
    {
        $this->assertTrue(config('queue.connections.database.after_commit'));
        $this->assertTrue(config('queue.connections.beanstalkd.after_commit'));
        $this->assertTrue(config('queue.connections.sqs.after_commit'));
        $this->assertTrue(config('queue.connections.redis.after_commit'));
    }
}
