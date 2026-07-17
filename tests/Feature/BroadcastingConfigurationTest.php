<?php

namespace Tests\Feature;

use Tests\TestCase;

class BroadcastingConfigurationTest extends TestCase
{
    public function test_pusher_websocket_configuration_is_exposed_to_the_echo_client(): void
    {
        config([
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
            'broadcasting.connections.pusher.client.host' => 'ws.example.test',
            'broadcasting.connections.pusher.client.port' => 443,
            'broadcasting.connections.pusher.client.scheme' => 'https',
        ]);

        $html = view('includes.broadcasting-meta')->render();

        $this->assertStringContainsString('name="pusher-app-key" content="test-key"', $html);
        $this->assertStringContainsString('name="pusher-app-cluster" content="mt1"', $html);
        $this->assertStringContainsString('name="pusher-app-host" content="ws.example.test"', $html);
        $this->assertStringContainsString('name="pusher-app-port" content="443"', $html);
        $this->assertStringContainsString('name="pusher-app-scheme" content="https"', $html);
    }

    public function test_dashboard_uses_the_versioned_echo_bundle_from_the_mix_manifest(): void
    {
        $html = view('includes.footer')->render();

        $this->assertStringContainsString('/assets/js/app.js?id=', $html);
        $this->assertStringNotContainsString('/assets/js/app.js?v=', $html);
    }
}
