<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification
    {
        use Queueable;

        protected $user;
        protected $password;

    /**
     * Create a new notification instance.
     */
        public function __construct($user, $password)
            {
                $this->user     = $user;
                $this->password = $password;
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
                    ->subject('Welcome - Your Login Details')
                    ->view('mail.new_user', ['user' => $this->user, 'password' => $this->password]);
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
