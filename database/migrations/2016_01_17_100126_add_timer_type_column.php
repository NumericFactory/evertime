<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimerTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timers', function (Blueprint $table){
            $table->enum('timer_type', ['evergreen', 'deadline'])->default('deadline')->after('name')->index();
            $table->tinyInteger('offset_days')->after('timer_type');
            $table->tinyInteger('offset_hours')->after('offset_days');
            $table->tinyInteger('offset_minutes')->after('offset_hours');
            $table->tinyInteger('offset_seconds')->after('offset_minutes');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timers', function (Blueprint $table){
            $table->dropColumn('timer_type');
            $table->dropColumn('offset_days');
            $table->dropColumn('offset_hours');
            $table->dropColumn('offset_minutes');
            $table->dropColumn('offset_seconds');
            
        });
    }
}
