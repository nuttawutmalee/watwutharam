<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionElementTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('option_element_types');

        Schema::create('option_element_types', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->string('item_type');
            $table->string('element_type');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('element_value')->nullable();
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
        Schema::dropIfExists('option_element_types');
    }
}
