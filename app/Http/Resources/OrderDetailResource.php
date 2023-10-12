<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
      'items' => OrderItemResource::collection($this->items),
      'invoice' => new InvoiceResource($this->invoice),
      'payment' => new PaymentResource($this->invoice->payment),
      'shipping_address' => $this->shipping->address,
      'shipping' => new ShippingResource($this->shipping),
      'notes' => $this->notes,
      'cancel_reason' => $this->cancel_reason,
      'status' => $this->status,
      'total_quantity' => $this->total_quantity,
      'total_weight' => $this->shipping->weight,
      'total_price' => $this->total_price,
      'shipping_cost' => $this->shipping_cost,
      'total_amount' => $this->invoice->amount,
      'payment_due_date' => $this->invoice->due_date,
      'confirmed_at' => $this->confirmed_at,
      'completed_at' => $this->completed_at,
      'cancelled_at' => $this->cancelled_at,
      'created_at' => $this->created_at,
    ];
  }
}
