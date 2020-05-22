<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeFkToCategoryRelations extends Migration
{
    /**
     * The tables to alter in this migration.
     *
     * @var array
     */
    protected $tables = [
        'category_channel',
        'category_customer_group',
        'product_categories',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign("{$tableName}_category_id_foreign");
                $table->foreign('category_id')
                ->references('id')->on('categories')
                ->onDelete('cascade');
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
