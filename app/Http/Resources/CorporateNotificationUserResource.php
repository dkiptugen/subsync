<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class CorporateNotificationUserResource extends JsonResource
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
                        "id"                 => $this->id,
                        'name'               => $this->name,
                        'email'              => $this->email,
                        'phone'              => $this->phone,
                        'verified'           => ($this->is_verified !== 0) ? TRUE : FALSE,
                        'verification_level' => $this->verification_count,
                        'subscription_date'  => $this->start_date,
                        'expires'            => $this->expiry_date,
                        "can_notify"         => (bool)($this->can_notify)
                    ];
                }
        }
