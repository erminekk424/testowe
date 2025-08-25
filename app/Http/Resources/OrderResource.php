<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'amount' => $this->amount,

            'items' => OrderItemResource::collection($this->items),
            'messages' => OrderMessageResource::collection($this->messages),
            'payments' => PaymentResource::collection($this->payments),
        ];
    }
}
