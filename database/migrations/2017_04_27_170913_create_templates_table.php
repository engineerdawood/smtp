<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject', 1024);
            $table->integer('campaign_id')->unsigned()->nullable();
            $table->mediumText('message');
            $table->tinyInteger('status')->default(0)->comment('[0 => Draft, 1 => In Queue, 2 => Fail, 3 => Sent]');
            $table->decimal('spam_score', 4, 2)->default(10);
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('templates');
    }
}
