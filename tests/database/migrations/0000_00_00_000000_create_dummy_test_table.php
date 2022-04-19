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
            $table->datetime('datetime_custom', 6);
            $table->timestamp('timestamp');
            $table->json('json');
            $table->json('array');
            $table->json('object');
            $table->json('collection');
            $table->text('text');
            $table->text('class_cast');
            $table->text('encrypted');
            $table->text('no_cast');

            if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
                $table->string('enum_string', 10);
                $table->integer('enum_integer');
                $table->integer('enum_arrayable');
                $table->integer('enum_jsonable');
                $table->string('enum_string_laravel', 10);
                $table->integer('enum_integer_laravel');
            }
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
