<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('template_items');

        Schema::create('template_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('variable_name')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('description')->nullable();
            $table->integer('display_order');
            $table->uuid('template_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->uuid('component_id')->nullable();
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
        Schema::dropIfExists('template_items');
    }
}
