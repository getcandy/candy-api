<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDraftColumnToTables extends Migration {

	/**
	 * The tables to alter in this migration
	 *
	 * @var array
	 */
	protected $tables = [
		'products',
		'product_variants',
		'routes',
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