<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency' => $this->currency->code,
            'balance' => $this->balance,
            'updated_at' => $this->updated_at,
        ];
    }
}
