<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxTimersColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table){
            $table->integer('max_timers_count')->default(3)->unsigned()->after('locale');
            $table->integer('max_frenzy_count')->default(3)->unsigned()->after('max_timers_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table){
            $table->dropColumn('max_timers_count');
            $table->dropColumn('max_frenzy_count');
        });
    }
}
