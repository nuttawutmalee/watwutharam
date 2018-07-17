<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComponentOptionIntegersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('component_option_integers');

        Schema::create('component_option_integers', function (Blueprint $table) {
            $table->uuid('component_option_id');
            $table->primary('component_option_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->integer('option_value')->nullable();
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
        Schema::dropIfExists('component_option_integers');
    }
}
