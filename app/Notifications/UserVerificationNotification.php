<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserVerificationNotification extends Notification implements ShouldBeEncrypted, ShouldQueue
    {
        use Queueable;

        public $user;
        public $link;

    /**
     * Create a new notification instance.
     */
        public function __construct($user,$link)
            {
                $this->user = $user;
                $this->link = $link;
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
                    ->subject('User Verification Notification')
                    ->view('mail.verification', ['user' => $this->user,'link'=>$this->link]);
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
