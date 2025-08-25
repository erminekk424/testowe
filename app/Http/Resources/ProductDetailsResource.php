<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            //            'uuid' => $this->uuid,
            //            'name' => $this->name,
            'description' => $this->description,
            //            'image' => Storage::url($this->image),

            //            'attributes' => $this->attributes,
            //            'type' => $this->type,

            //            'price' => $this->price,

            //            'quantity' => $this->quantity,
        ];
    }
}
