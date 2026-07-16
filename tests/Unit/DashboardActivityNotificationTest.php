<?php

namespace Tests\Unit;

use App\Notifications\DashboardActivityNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use stdClass;

class DashboardActivityNotificationTest extends TestCase
{
    public function test_notification_is_queued_encrypted_and_broadcast_with_database_data(): void
    {
        $notification = new DashboardActivityNotification(
            'Payment received',
            'Transaction ABC was paid.',
            'check-circle',
            'success',
            '/manage/subscription',
        );
        $notifiable = new stdClass;
        $expected = [
            'title' => 'Payment received',
            'message' => 'Transaction ABC was paid.',
            'icon' => 'check-circle',
            'tone' => 'success',
            'url' => '/manage/subscription',
        ];

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertInstanceOf(ShouldBeEncrypted::class, $notification);
        $this->assertTrue(Str::isUlid($notification->id));
        $this->assertSame(['database', 'broadcast'], $notification->via($notifiable));
        $this->assertSame($expected, $notification->toArray($notifiable));
        $this->assertSame($expected, $notification->toBroadcast($notifiable)->data);
        $this->assertSame('dashboard.activity', $notification->databaseType($notifiable));
        $this->assertSame('dashboard.activity', $notification->broadcastType());
        $this->assertNotSame($notification->id, $notification->forDelivery()->id);
    }
}
