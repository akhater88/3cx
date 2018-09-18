<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLogReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('log_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('historyid',255);
            $table->string('callid',255);
            $table->string('duration',255)->nullable()->defualt('');
            $table->dateTime('time_start');
            $table->dateTime('time_answered');
            $table->dateTime('time_end');
            $table->string('reason_terminated',255);
            $table->string('from_no',255);
            $table->string('to_no',255);
            $table->string('from_dn',255);
            $table->string('to_dn',255);
            $table->string('dial_no',255);
            $table->string('reason_changed',255);
            $table->string('final_number',255);
            $table->string('final_dn',255);
            $table->text('chain');
            $table->string('from_type',255);
            $table->string('to_type',255);
            $table->string('final_type',255);
            $table->string('from_dispname',255);
            $table->string('to_dispname',255);
            $table->string('final_dispname',255);
            $table->string('call_type',20);
            $table->string('call_sub_type',20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_report');
    }
}
