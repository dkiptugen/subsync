<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DashboardActivityNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $icon,
        public readonly string $tone,
        public readonly string $url,
    ) {
        $this->id = (string) Str::ulid();
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function databaseType(object $notifiable): string
    {
        return 'dashboard.activity';
    }

    public function broadcastType(): string
    {
        return 'dashboard.activity';
    }

    public function forDelivery(): self
    {
        $notification = clone $this;
        $notification->id = (string) Str::ulid();

        return $notification;
    }

    /**
     * @return array{title: string, message: string, icon: string, tone: string, url: string}
     */
    private function payload(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'tone' => $this->tone,
            'url' => $this->url,
        ];
    }
}
