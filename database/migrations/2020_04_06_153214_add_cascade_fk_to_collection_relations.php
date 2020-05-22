<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeFkToCollectionRelations extends Migration
{
    /**
     * The tables to alter in this migration.
     *
     * @var array
     */
    protected $tables = [
        'channel_collection',
        'collection_customer_group',
        'collection_product',
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
                $table->dropForeign("{$tableName}_collection_id_foreign");
                $table->foreign('collection_id')
                ->references('id')->on('collections')
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
