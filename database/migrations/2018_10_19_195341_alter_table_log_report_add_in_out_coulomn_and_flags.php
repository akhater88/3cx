<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableLogReportAddInOutCoulomnAndFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_report', function($table) {
            $table->string('extintion_inbound',255)->after('call_sub_type')->nullable()->defualt('');
            $table->string('extintion_outbound',255)->after('call_sub_type')->nullable()->defualt('');
            $table->string('inbound_outbound_flag',255)->after('call_sub_type')->nullable()->defualt('');
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
            $table->dropColumn('extintion_inbound');
            $table->dropColumn('extintion_outbound');
            $table->dropColumn('inbound_outbound_flag');
        });
    }
}
