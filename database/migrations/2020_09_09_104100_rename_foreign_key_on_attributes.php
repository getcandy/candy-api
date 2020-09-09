<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameForeignKeyOnAttributes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->integer('attribute_group_id')->unsigned()->after('id')->nullable()->onDelete('SET NULL');
            $table->foreign('attribute_group_id')->references('id')->on('attribute_groups');
        });

        DB::table('attributes')->update([
            'attribute_group_id' => DB::RAW('attributes.group_id'),
        ]);

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign('attributes_group_id_foreign');
            $table->dropColumn('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
