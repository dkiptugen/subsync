<?php

namespace Tests\Unit;

use App\Events\DashboardUpdated;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use PHPUnit\Framework\TestCase;

class DashboardUpdatedTest extends TestCase
{
    public function test_dashboard_updates_broadcast_on_the_private_dashboard_channel(): void
    {
        $event = new DashboardUpdated('transaction', 'updated');
        $channels = $event->broadcastOn();

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-dashboard', $channels[0]->name);
        $this->assertSame('dashboard.updated', $event->broadcastAs());
        $this->assertSame('default', $event->broadcastQueue());
    }
}
