<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAltContactToContactDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('alt_contact_number')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn('alt_contact_number');
        });
    }
}
