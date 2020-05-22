<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDraftColumnsToCategoryAssociations extends Migration
{

	/**
	 * The tables to alter in this migration
	 *
	 * @var array
	 */
	protected $tables = [
		'category_channel',
		'category_customer_group',
		'product_categories'
	];

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		foreach ($this->tables as $table) {
			Schema::table($table, function (Blueprint $table) {
				$table->drafting();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}