<?php

namespace App\Http\Resources;

use App\Models\ProductBonus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTypeResource extends JsonResource
{
    /**
     * @param  Request $request
     * @return array
     * @throws \JsonException
    */
    public function toArray($request): array
    {
        return [
            'type' => $this->name,
            'bonuses' => [
                'firstPayment' => $this->productBonuses->where('is_active', true)->where('type', ProductBonus::FIRST_PAYMENT)->first()->amount ?? 0,
                'repeatedPayment' => $this->productBonuses->where('is_active', true)->where('type', ProductBonus::REPEATED_PAYMENT)->first()->amount ?? 0,
            ],
        ];
    }
}
