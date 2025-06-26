<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToRequestApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_applications', function (Blueprint $table) {
            $table->time('clock_in')->nullable()->after('applied_at');
            $table->time('clock_out')->nullable()->after('clock_in');
            $table->text('note')->nullable()->after('clock_out');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_applications', function (Blueprint $table) {
            $table->dropColumn(['clock_in', 'clock_out', 'note']);
        });
    }
}
