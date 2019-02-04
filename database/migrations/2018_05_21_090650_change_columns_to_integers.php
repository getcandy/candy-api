<?php

use Illuminate\Support\Facades\Schema;
use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class ChangeColumnsToIntegers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setUp();
        $this->remapDecimalsToIntegers();
        $this->cleanUp();
    }

    protected function setUp()
    {
        Schema::table('discount_rewards', function (Blueprint $table) {
            $table->integer('value_new')->unsigned()->nullable()->default(0)->after('value');
        });

        Schema::table('order_discounts', function (Blueprint $table) {
            $table->integer('amount_new')->unsigned()->default(0)->after('amount');
        });

        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->integer('rate_new')->unsigned()->default(0)->after('rate');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('amount_new')->unsigned()->default(0)->after('amount');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 15, 5)->change();
        });

        Schema::table('product_pricing_tiers', function (Blueprint $table) {
            $table->decimal('price', 15, 5)->change();
        });

        Schema::table('product_customer_prices', function (Blueprint $table) {
            $table->decimal('price', 15, 5)->change();
        });
    }

    protected function cleanUp()
    {
        Schema::table('discount_rewards', function (Blueprint $table) {
            $table->dropColumn('value');
        });
        Schema::table('discount_rewards', function (Blueprint $table) {
            $table->renameColumn('value_new', 'value');
        });

        Schema::table('order_discounts', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('order_discounts', function (Blueprint $table) {
            $table->renameColumn('amount_new', 'amount');
        });

        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->dropColumn('rate');
        });

        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->renameColumn('rate_new', 'rate');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('amount_new', 'amount');
        });
    }

    protected function remapDecimalsToIntegers()
    {
        // DB::table('discount_rewards')->update(['value_new' => DB::raw('IF(value, 100 * value, 0)')]);
        // DB::table('order_discounts')->update(['amount_new' => DB::raw('100 * amount')]);
        // DB::table('shipping_prices')->update(['rate_new' => DB::raw('100 * rate')]);
        // DB::table('transactions')->update(['amount_new' => DB::raw('100 * amount')]);
    }

    /**
     * Remaps the orders for our new way.
     *
     * @return void
     */
    protected function addNewColumns()
    {
        /*
         * Orders
         */
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('sub_total')->unsigned()->default(0)->after('user_id');
            $table->integer('delivery_total')->unsigned()->default(0)->after('sub_total');
            $table->integer('discount_total')->unsigned()->default(0)->after('delivery_total');
            $table->integer('tax_total')->unsigned()->default(0)->after('discount_total');
            $table->integer('order_total')->unsigned()->default(0)->after('tax_total');
        });

        /*
         * Order lines
         */
        Schema::table('order_lines', function (Blueprint $table) {
            $table->boolean('is_shipping')->unsigned()->default(false)->after('order_id');
            $table->integer('line_total')->unsigned()->default(0)->after('total');
            $table->integer('unit_price')->unsigned()->default(0)->after('line_total');
            $table->integer('discount_total')->unsigned()->default(0)->after('unit_price');
            $table->integer('tax_total')->unsigned()->default(0)->after('discount_total');
            $table->decimal('tax_rate', 10, 6)->unsigned()->default(0)->after('tax_total');
            $table->softDeletes();
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
    }

    protected function repopulateOrders()
    {
        $orders = Order::withoutGlobalScopes()->whereNotNull('placed_at')->with(['discounts', 'lines'])->orderBy('created_at', 'desc')->get();

        $i = 0;

        foreach ($orders as $order) {
            $discount = $order->discounts->first();

            $taxrate = Tax::where('default', '=', true)->first();

            // foreach ($order->lines as $line) {

            //     $taxrate = Tax::where('default', '=', true)->first();

            //     $productPrices = $this->getProductPrices($line->sku);

            //     $totalHasTax = false;

            //     $reference = explode('-', $order->reference);

            //     if (count($reference) == 3) {
            //         $totalHasTax = true;
            //     }

            //     if ($totalHasTax) {
            //         $lineTotalWithoutTax = $line->total / (1 + ($taxrate->percentage / 100));
            //         $unitCost = $lineTotalWithoutTax / $line->quantity;
            //         $taxAmount = $line->total - $lineTotalWithoutTax;
            //         $lineTotal = $lineTotalWithoutTax;
            //     } else {
            //         $unitCost = $line->total / $line->quantity;
            //         $taxAmount = ($line->total * 1.2) - $line->total;
            //         $lineTotal = $line->total;
            //     }

            //     $line->update([
            //         'tax_rate' => $taxrate->percentage,
            //         'line_total' => $lineTotal * 100,
            //         'unit_price' => $unitCost * 100,
            //         'tax_total' => $taxAmount * 100,
            //     ]);
            // }

            // All order totals are inclusive of tax.

            $totalExlTax = round($order->total / (1 + ($taxrate->percentage / 100)), 2);

            $order->sub_total = $totalExlTax * 100;
            $order->delivery_total = $order->shipping_total; // Without tax.
            $order->order_total = $order->total * 100;
            // Set order delivery total
            $tax = ($order->delivery_total * 1.2) - $order->delivery_total;
            $tax += $order->vat;

            if ($tax * 100 < 0) {
                $order->tax_total = 0;
            } else {
                $order->tax_total = $tax * 100;
            }
            // $order->tax_total = $tax * 100;

            $order->save();
        }
    }

    protected function getProductPrices($sku)
    {
        $product = ProductVariant::where('sku', '=', $sku)->with(['tiers', 'customerPricing'])->first();
        if (! $product) {
            return false;
        }
        $prices = [$product->price];
        $prices = array_merge($prices, $product->customerPricing->pluck('price')->toArray());
        $prices = array_merge($prices, $product->tiers->pluck('price')->toArray());

        return $prices;
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

                    $lineAmount = $line->line_total - ($line->line_total * $decimal);

                    $taxAmount = $lineAmount - ($lineAmount / (1 + ($taxrate->percentage / 100)));

                    // $line->update([
                    //     'discount_total' => $line->line_total - $lineAmount,
                    //     'tax_total' => $taxAmount,
                    // ]);
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
                $order->lines()->create([
                    'total' => $order->shipping_total,
                    'quantity' => 1,
                    'line_total' => $order->shipping_total * 100,
                    'unit_price' => $order->shipping_total * 100,
                    'tax_total' => (($order->shipping_total * 1.2) - $order->shipping_total) * 100,
                    'description' => $order->shipping_method ?: 'Standard',
                    'is_shipping' => true,
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
        // @todo Down method
    }
}
