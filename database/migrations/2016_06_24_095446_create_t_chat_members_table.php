<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTChatMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_chat_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->enum('status', ['creator', 'administrator', 'member', 'left', 'kicked']);
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
        Schema::drop('t_chat_members');
    }
}
