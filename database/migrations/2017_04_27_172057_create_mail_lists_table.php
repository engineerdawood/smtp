<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned()->nullable();
            $table->integer('email_id')->unsigned()->nullable();
            $table->tinyInteger('is_viewed')->default(0)->comment('[0 => No, 1 => Yes]');
            $table->tinyInteger('status')->default(0)->comment('[0 => In Queue, 1 => Sent, 2 => Failed]');

            $table->foreign('template_id')->references('id')->on('templates')->onDelete('SET NULL');
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_lists');
    }
}
