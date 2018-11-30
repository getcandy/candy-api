<?php

namespace GetCandy\Api\Core\Orders\Services;

use PriceCalculator;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;

class OrderLineService extends BaseService
{
    protected $orders;

    public function __construct(OrderService $orders)
    {
        $this->orders = $orders;
        $this->model = new OrderLine;
    }

    /**
     * Add a manual order line.
     *
     * @param string $orderId
     * @param array $data
     * @return Order
     */
    public function store($orderId, $data = [], $manual = true)
    {
        $order = $this->orders->getByHashedId($orderId);

        if (isset($data['line_total'])) {
            $lineTotal = $data['line_total'];
        } else {
            $lineTotal = $data['unit_price'] * $data['quantity'];
        }

        if (! isset($data['unit_price'])) {
            $unitPrice = $data['line_total'] / $data['quantity'];
        } else {
            $unitPrice = $data['unit_price'];
        }

        $pricing = PriceCalculator::get($lineTotal, $data['tax_rate'], $data['quantity'] ?? 1);

        $order->lines()->create([
            'description' => $data['description'],
            'is_shipping' => $data['is_shipping'] ?? false,
            'quantity' => $data['quantity'],
            'is_manual' => $manual,
            'line_total' => $pricing->total_cost,
            'unit_price' => $pricing->unit_cost,
            'tax_total' => $pricing->total_tax,
            'variant' => $data['variant'] ?? null,
            'sku' => $data['sku'] ?? null,
            'discount_total' => $data['discount_total'] ?? 0,
        ]);

        event(new OrderSavedEvent($order));

        return $order->fresh();
    }

    /**
     * Delete an order line.
     *
     * @param string $lineId
     * @return Order
     */
    public function delete($lineId)
    {
        $realId = $this->model->decodeId($lineId);
        $line = $this->model->find($realId);

        $line->delete();

        return $line->order;
    }
}
