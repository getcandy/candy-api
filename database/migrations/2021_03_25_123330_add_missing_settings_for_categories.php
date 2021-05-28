<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMissingSettingsForCategories extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Do we already have settings for categories?
        $settingsExist = DB::table('settings')->whereHandle('categories')->exists();

        if (! $settingsExist) {
            DB::table('settings')->insert([
                'name' => 'Categories',
                'handle' => 'categories',
                'content' => '{"transforms": ["large_thumbnail"], "asset_source": "categories"}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('settings')->whereHandle('categories')->delete();
    }
}
