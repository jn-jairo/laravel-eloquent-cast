<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDummyTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dummy', function (Blueprint $table) {
            $table->char('uuid', 16)->charset('binary');
            $table->boolean('boolean');
            $table->integer('integer');
            $table->float('float', 10, 2);
            $table->decimal('decimal', 10, 2);
            $table->date('date');
            $table->datetime('datetime');
            $table->timestamp('timestamp');
            $table->json('json');
            $table->json('array');
            $table->json('object');
            $table->json('collection');
            $table->text('text');
            $table->text('no_cast');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dummy');
    }
}
