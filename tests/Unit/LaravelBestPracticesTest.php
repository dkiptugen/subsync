<?php

namespace Tests\Unit;

use App\Models\Agent;
use App\Models\ApplePayLogs;
use App\Models\Coupon;
use App\Models\DataMigrationUpload;
use App\Models\MediaEvent;
use App\Models\MpesaBlacklist;
use App\Models\Point;
use App\Models\PointHistory;
use App\Models\Subscription;
use App\Models\UserFlowEvent;
use App\Notifications\NewUserNotification;
use App\Notifications\PasswordResetRequest;
use App\Notifications\UserVerificationNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LaravelBestPracticesTest extends TestCase
{
    #[DataProvider('massAssignableModelProvider')]
    public function test_models_do_not_allow_unrestricted_mass_assignment(string $modelClass): void
    {
        $model = (new ReflectionClass($modelClass))->newInstanceWithoutConstructor();

        $this->assertNotSame([], $model->getGuarded());
    }

    #[DataProvider('queuedNotificationProvider')]
    public function test_sensitive_notifications_are_queued_and_encrypted(string $notificationClass): void
    {
        $this->assertTrue(is_subclass_of($notificationClass, ShouldQueue::class));
        $this->assertTrue(is_subclass_of($notificationClass, ShouldBeEncrypted::class));
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function massAssignableModelProvider(): array
    {
        return [
            Agent::class => [Agent::class],
            ApplePayLogs::class => [ApplePayLogs::class],
            Coupon::class => [Coupon::class],
            DataMigrationUpload::class => [DataMigrationUpload::class],
            MediaEvent::class => [MediaEvent::class],
            MpesaBlacklist::class => [MpesaBlacklist::class],
            Point::class => [Point::class],
            PointHistory::class => [PointHistory::class],
            Subscription::class => [Subscription::class],
            UserFlowEvent::class => [UserFlowEvent::class],
        ];
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function queuedNotificationProvider(): array
    {
        return [
            NewUserNotification::class => [NewUserNotification::class],
            PasswordResetRequest::class => [PasswordResetRequest::class],
            UserVerificationNotification::class => [UserVerificationNotification::class],
        ];
    }
}
