<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('components');

        Schema::create('components', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('variable_name')->unique();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('components');
    }
}
