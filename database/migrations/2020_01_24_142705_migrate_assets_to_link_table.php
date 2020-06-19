<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateAssetsToLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assetables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('asset_id')->unsigned();
            $table->boolean('primary')->default(false);
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->morphs('assetable');
            $table->string('position')->index();
            $table->timestamps();
        });

        DB::table('assets')->orderBy('assetable_id')->chunk(100, function ($assets) {
            DB::table('assetables')->insert(
                $assets->map(function ($asset) {
                    return [
                        'asset_id' => $asset->id,
                        'assetable_id' => $asset->assetable_id,
                        'primary' => $asset->primary,
                        'assetable_type' => $asset->assetable_type,
                        'position' => $asset->position,
                        'created_at' => $asset->created_at,
                        'updated_at' => $asset->updated_at,
                    ];
                })->toArray()
            );
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('assetable_id');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('assetable_type');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('position');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('primary');
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->integer('position')->after('asset_source_id');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->string('assetable_type')->after('location');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->bigInteger('assetable_id')->after('assetable_type');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('primary')->after('assetable_id')->default(false);
        });

        Schema::dropIfExists('assetables');
    }
}
