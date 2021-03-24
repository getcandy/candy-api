<?php

use GetCandy\Api\Core\Languages\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorRoutesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $languages = DB::table('routes')->groupBy('locale')->pluck('locale')->mapWithKeys(function ($locale) {
            $language = Language::whereCode($locale)->first();

            // If we can't find a language, then we use the first one we can get hold of.
            // Routes will need a language id.
            if (!$language) {
                $language = Language::whereDefault(true)->first();
            }

            return [$locale => $language];
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->integer('language_id')->after('id')->unsigned()->nullable();
            $table->foreign('language_id')->references('id')->on('languages');
        });

        foreach ($languages as $locale => $language) {
            DB::table('routes')->whereLocale($locale)->update([
                'language_id' => $language->id,
            ]);
        }

        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('path');
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->index(['element_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('report_exports');
    }
}
