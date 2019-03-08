<?php

namespace Tests\Stubs;

use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\Models\Transaction;

class TestPaymentDriver
{
    protected $order;

    protected $token;

    protected $fields = [];

    public function getName()
    {
        return 'TEST_PAYMENT';
    }

    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    public function fields($fields = [])
    {
        $this->fields = $fields;

        return $this;
    }

    public function token($token)
    {
        $this->token = $token;

        return $this;
    }

    public function validate($token)
    {
        if ($token == 1234) {
            return false;
        }

        return true;
    }

    public function charge()
    {
        if ($this->token == 'threed') {
            return new ThreeDSecureResponse;
        }
        $transaction = Transaction::forceCreate([
            'order_id' => $this->order->id,
            'amount' => $this->order->sub_total,
            'refund' => false,
            'driver' => 'sagepay',
            'success' => true,
            'transaction_id' => 'TESTPAYMENT',
            'merchant' => 'getcandy',
            'status' => 'OK',
        ]);

        $response = new PaymentResponse(true);
        $response->transaction($transaction);

        return $response;
    }
}
