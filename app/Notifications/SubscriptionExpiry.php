<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiry extends Notification implements ShouldBeEncrypted, ShouldQueue
    {
        use Queueable;

    /**
     * @var mixed
     */
        public $product;
    /**
     * @var mixed
     */
        public $user;
    /**
     * @var mixed
     */
        public $expiry_date;

        public $renew_link;

    /**
     * Create a new notification instance.
     */
        public function __construct($user, $product, $expiryDate, $renewLink)
            {
                $this->product     = $product;
                $this->user        = $user;
                $this->expiry_date = $expiryDate;
                $this->renew_link  = $renewLink;
            }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
        public function via(object $notifiable)
        : array
            {
                return ['mail'];
            }

    /**
     * Get the mail representation of the notification.
     */
        public function toMail(object $notifiable)
        : MailMessage
            {
                return (new MailMessage())
                    ->subject('Renew Your Nation ePaper Subscription Today!')
                    ->view('mail.subscription-expiry', ['user'       => $this->user,
                                                        'product'    => $this->product,
                                                        'expirydate' => $this->expiry_date,
                                                        'renew_link' => $this->renew_link
                    ]);
            }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
        public function toArray(object $notifiable)
        : array
            {
                return [
                    //
                ];
            }
    }
