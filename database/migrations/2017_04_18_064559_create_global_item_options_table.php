<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalItemOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('global_item_options');

        Schema::create('global_item_options', function (Blueprint $table) {
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
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_visible')->default(true);
            $table->uuid('global_item_id');
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
        Schema::dropIfExists('global_item_options');
    }
}
