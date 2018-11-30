<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateOrderPlacedAtDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $orders = \GetCandy\Api\Core\Orders\Models\Order::withoutGlobalScopes()->with(['transactions' => function ($q) {
            $q->where('success', '=', 1);
        }])->whereIn('status', ['dispatched', 'payment-received'])->get();

        foreach ($orders as $order) {
            $paymentDate = $order->created_at;
            $transaction = $order->transactions->first();

            if ($transaction) {
                $paymentDate = $transaction->created_at;
            }

            $order->placed_at = $paymentDate;
            $order->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
