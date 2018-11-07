<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('attribute_groups');
            $table->json('name');
            $table->string('handle')->unique();
            $table->integer('position');
            $table->boolean('variant')->default(false);
            $table->boolean('searchable')->default(false);
            $table->boolean('filterable')->default(false);
            $table->boolean('system')->default(false);
            $table->boolean('channeled')->default(false);
            $table->boolean('translatable')->default(true);
            $table->enum(
                'type',
                ['text', 'textarea', 'select', 'radio', 'richtext', 'checkbox', 'date', 'time', 'checkbox_group', 'radio_group', 'toggle', 'number']
            )->default('text');
            $table->boolean('required')->default(false);
            $table->json('lookups')->nullable();
            $table->timestamps();
            $table->index(['handle']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['handle']);
        });
        Schema::dropIfExists('attributes');
    }
}
