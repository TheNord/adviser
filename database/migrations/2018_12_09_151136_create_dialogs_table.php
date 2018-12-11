<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDialogsTable extends Migration
{
    public function up()
    {
        Schema::create('advert_dialogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('advert_id')->references('id')->on('advert_adverts')->onDelete('CASCADE');
            $table->integer('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->integer('client_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->integer('user_new_messages')->nullable();
            $table->integer('client_new_messages')->nullable();
            $table->timestamps();
        });

        Schema::create('advert_dialog_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dialog_id')->references('id')->on('advert_dialogs')->onDelete('CASCADE');
            $table->integer('user_id')->references('id')->on('users');
            $table->string('message');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('advert_dialogs');
        Schema::dropIfExists('advert_dialog_messages');
    }
}
