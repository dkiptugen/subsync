<?php
    
    namespace App\Http\Resources;
    
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    
    class PaymentMethodResource extends JsonResource
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
                        'name'       => $this->name,
                        'identifier' => $this->identifier,
                        'provider'   => $this->provider,
                        'type'       => $this->type,
                        'code'       => $this->condiguration['code'] ?? null,
                    ];
                }
        }
