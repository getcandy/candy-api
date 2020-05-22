<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeToProductVariantRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing_tiers', function (Blueprint $table) {
            $table->dropForeign('product_pricing_tiers_product_variant_id_foreign');
            $table->foreign('product_variant_id')
            ->references('id')->on('product_variants')
            ->onDelete('cascade');
        });

        Schema::table('discount_reward_products', function (Blueprint $table) {
            $table->dropForeign('discount_reward_products_product_id_foreign');
            $table->foreign('product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
        });

        Schema::table('product_associations', function (Blueprint $table) {
            $table->dropForeign('product_associations_product_id_foreign');
            $table->foreign('product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign('product_categories_product_id_foreign');
            $table->foreign('product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
        });

        Schema::table('product_recommendations', function (Blueprint $table) {
            $table->dropForeign('product_recommendations_related_product_id_foreign');
            $table->foreign('related_product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
            $table->dropForeign('product_recommendations_product_id_foreign');
            $table->foreign('product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign('product_variants_product_id_foreign');
            $table->foreign('product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
        });

        Schema::table('customer_group_product', function (Blueprint $table) {
            $table->dropForeign('customer_group_product_product_id_foreign');
            $table->foreign('product_id')
            ->references('id')->on('products')
            ->onDelete('cascade');
        });
    }

    public function down()
    {
    }
}
