<?php

use Illuminate\Support\Facades\Schema;
use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Schema\Blueprint;
use GetCandy\Api\Core\Orders\Models\Order;
use Illuminate\Database\Migrations\Migration;
use GetCandy\Api\Core\Orders\Models\OrderLine;

class ModifyOrdersAndLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->remapDatabaseColumns();
        $this->realignOrderLines();
        $this->applyDiscountsToOrderLines();
        $this->addShippingOrderLines();
        $this->removeShippingFromOrders();
    }

    /**
     * Remaps the orders for our new way.
     *
     * @return void
     */
    protected function remapDatabaseColumns()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->renameColumn('total', 'line_amount');
        });
        Schema::table('order_lines', function (Blueprint $table) {
            $table->string('variant')->nullable()->change();
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->string('sku')->nullable()->change();
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->renameColumn('product', 'description');
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->decimal('tax_rate', 10, 2)->default(0)->after('tax');
            $table->decimal('discount', 10, 2)->after('line_amount')->default(0);
            $table->boolean('shipping')->after('order_id')->default(false);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('vat');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }

    /**
     * Goes through each order line and its the corresponding tax.
     *
     * @return void
     */
    protected function realignOrderLines()
    {
        // Get all lines
        $lines = OrderLine::all();
        // Get default tax rate
        $taxrate = Tax::where('default', '=', true)->first();

        DB::transaction(function () use ($lines, $taxrate) {
            foreach ($lines as $line) {
                $itemCost = $line->line_amount / $line->quantity; // With Tax
                $costNoTax = round($itemCost / (1 + ($taxrate->percentage / 100)), 2); // Without tax

                $lineAmount = $costNoTax * $line->quantity; // Without tax
                $taxAmount = TaxCalculator::setTax($taxrate)->amount($lineAmount);

                $line->update([
                    'line_amount' => $lineAmount,
                    'tax' => $taxAmount,
                    'tax_rate' => $taxrate->percentage,
                ]);
            }
        });
    }

    /**
     * Get any orders with discounts and update the order lines.
     *
     * @return void
     */
    protected function applyDiscountsToOrderLines()
    {
        DB::transaction(function () {
            $orders = Order::withoutGlobalScopes()->whereHas('discounts')->get();
            $taxrate = Tax::where('default', '=', true)->first();

            foreach ($orders as $order) {
                $lines = $order->lines;
                $discount = $order->discounts->first();

                foreach ($order->lines as $line) {
                    // Remove the discount from the line amount then recalc the tax and save.
                    $decimal = $discount->amount / 100;
                    $lineAmount = $line->line_amount - ($line->line_amount * $decimal);

                    $taxAmount = TaxCalculator::setTax($taxrate)->amount($lineAmount);

                    $line->update([
                        'discount' => $line->line_amount * $decimal,
                        'tax' => $taxAmount,
                    ]);
                }
            }
        });
    }

    /**
     * Add shipping order lines.
     *
     * @return void
     */
    protected function addShippingOrderLines()
    {
        DB::transaction(function () {
            $orders = Order::withoutGlobalScopes()->get();
            $taxrate = Tax::where('default', '=', true)->first();

            foreach ($orders as $order) {
                $line = new OrderLine;
                $taxAmount = TaxCalculator::setTax($taxrate)->amount($order->shipping_total);

                $order->lines()->create([
                    'quantity' => 1,
                    'line_amount' => $order->shipping_total,
                    'tax' => $taxAmount,
                    'description' => $order->shipping_method ?: 'Standard',
                    'shipping' => true,
                    'tax_rate' => $taxrate->percentage,
                ]);
            }
        });
    }

    /**
     * Removes the shipping bits from orders table.
     *
     * @return void
     */
    protected function removeShippingFromOrders()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_total');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('driver');
        });
    }
}
