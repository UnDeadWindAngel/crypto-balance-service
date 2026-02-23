<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'type' => $this->type,
            'amount_total' => $this->amount_total,
            'amount_principal' => $this->amount_principal,
            'amount_fee_network' => $this->amount_fee_network,
            'amount_fee_service' => $this->amount_fee_service,
            'currency' => $this->currency->code,
            'status' => $this->status,
            'external_id' => $this->external_id,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
