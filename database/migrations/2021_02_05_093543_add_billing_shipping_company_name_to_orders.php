<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingShippingCompanyNameToOrders extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('company_name');
            if (!Schema::hasColumn('orders', 'billing_company_name')) {
                $table->string('billing_company_name')->after('billing_email')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'shipping_company_name')) {
                $table->string('shipping_company_name')->after('billing_email')->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('company_name')->nullable();
            $table->dropIfExists('billing_company_name');
            $table->dropIfExists('shipping_company_name');
        });
    }
}
