<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSubscriptionNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
    {
        use Queueable;

        public $product;
        public $user;

    /**
     * Create a new notification instance.
     */
        public function __construct($user, $product)
            {
                $this->user    = $user;
                $this->product = $product;
            }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
        public function via(object $notifiable): array
            {
                return ['mail'];
            }

    /**
     * Get the mail representation of the notification.
     */
        public function toMail(object $notifiable): MailMessage
            {
                return (new MailMessage())
                    ->subject('Successful Subscription')
                    ->view('mail.new_subscription', ['user' => $this->user, 'product' => $this->product]);
            }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
        public function toArray(object $notifiable): array
            {
                return [
                    //
                ];
            }
    }
