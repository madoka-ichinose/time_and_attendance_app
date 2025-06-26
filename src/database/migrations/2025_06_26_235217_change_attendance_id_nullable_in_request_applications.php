<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAttendanceIdNullableInRequestApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id')->nullable()->change();
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
            $table->unsignedBigInteger('attendance_id')->nullable(false)->change();
        });
    }
}
