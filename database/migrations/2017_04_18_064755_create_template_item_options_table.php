<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateItemOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('template_item_options');

        Schema::create('template_item_options', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('variable_name');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('description')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_required')->default(false);
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
            $table->uuid('template_item_id');
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
        Schema::dropIfExists('template_item_options');
    }
}
