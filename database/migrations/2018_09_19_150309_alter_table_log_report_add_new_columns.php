<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableLogReportAddNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_report', function($table) {
            $table->string('total_waiting_time',255)->after('call_sub_type')->nullable()->defualt('');
            $table->string('total_call_time',255)->after('call_sub_type')->nullable()->defualt('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_report', function($table) {
            $table->dropColumn('total_waiting_time');
            $table->dropColumn('total_call_time');
        });
    }
}
