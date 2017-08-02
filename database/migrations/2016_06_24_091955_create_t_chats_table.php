<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_chats', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->enum('type', [
                'private',
                'group',
                'supergroup',
                'channel',
            ]);
            $table->string('title')->nullable();
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
        Schema::drop('t_chats');
    }
}
