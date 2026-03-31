<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class NotificationResource extends JsonResource
        {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
            public function toArray(Request $request)
            : array
                {
                    return [
                        "id"                 => $this->user->id,
                        "name"               => $this->user->name,
                        "email"              => $this->user->email,
                        "phone"              => $this->user->phone,
                        "subscription_date"  => $this->subscription_date,
                        "expires"            => $this->expiry_date,
                        'verified'           => ($this->user->is_verified !== 0) ? TRUE : FALSE,
                        "verification_level" => $this->user->verification_count,
                        "can_notify"         => (bool)($this->user->can_notify)
                    ];
                }
        }
