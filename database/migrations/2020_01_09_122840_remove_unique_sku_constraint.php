<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueSkuConstraint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('product_variants', function(Blueprint $table)
		{
			$table->dropUnique(['sku']);
			$table->index('sku');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('product_variants', function(Blueprint $table)
		{
			$table->addUnique(['sku']);
		});
	}

}