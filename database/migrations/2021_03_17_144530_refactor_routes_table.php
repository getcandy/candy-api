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
            return [$locale => Language::whereCode($locale)->first()];
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
