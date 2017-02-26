<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->string('key')->unique();
            $table->string('title');
            $table->string('link');
            $table->string('placement');
            $table->text('description');
            $table->string('html_link')->nullable();
            $table->string('event_id')->nullable();
            $table->dateTimeTz('start');
            $table->dateTimeTz('stop');
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
        Schema::drop('quests');
    }
}
