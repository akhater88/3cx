<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableLogReportAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_report', function($table) {
            $table->string('extintion_missed_call',255)->after('call_sub_type')->nullable()->defualt('');
            $table->string('name_missed_call',255)->after('call_sub_type')->nullable()->defualt('');
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
            $table->dropColumn('name_missed_call');
            $table->dropColumn('extintion_missed_call');
        });
    }
}
