<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequest extends Notification
    {
        use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
        public $user, $endpoint, $redirect_url, $token, $channel,$created_at;

        public function __construct($user, $endpoint, $channel, $redirect_url, $token,$created_at)
            {

                $this->user         = $user;
                $this->endpoint     = $endpoint;
                $this->redirect_url = $redirect_url;
                $this->token        = $token;
                $this->channel      = $channel;
                $this->created_at   = $created_at;
            }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
        public function via($notifiable)
            {
                return ['mail'];
            }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
        public function toMail($notifiable)
            {
                //dd($this->user->toArray());
                return (new MailMessage())
                    ->subject('Password Reset Request Notification')
                    ->view('mail.password', ['user' => $this->user, 'endpoint' => $this->endpoint, 'channel' => $this->channel, 'token' => $this->token, 'redirect_url' => $this->redirect_url,'created_at'=>$this->created_at]);
            }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
        public function toArray($notifiable)
            {
                return [
                    //
                ];
            }
    }
