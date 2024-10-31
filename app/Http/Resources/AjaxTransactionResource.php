<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AjaxTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'invoice' => $this->invoice,
            'target' => $this->target,
            'buyer_sku_code' => $this->buyer_sku_code,
            'product_name' => $this->product_name,
            'price' => $this->price,
            'customer_no' => $this->customer_no,
            'customer_name' => $this->customer_name,
            'admin' => $this->admin,
            'description' => $this->description,
            'message' => $this->message,
            'sn' => $this->sn,
            'selling_price' => $this->selling_price,
            'tarif' => $this->tarif,
            'daya' => $this->daya,
            'billing' => $this->billing,
            'detail' => $this->detail,
            'status' => $this->status,
            'type' => $this->type,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at,
        ];
    }
}
