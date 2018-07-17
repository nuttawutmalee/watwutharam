<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateItemOptionDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('template_item_option_dates');

        Schema::create('template_item_option_dates', function (Blueprint $table) {
            $table->uuid('template_item_option_id');
            $table->primary('template_item_option_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('option_value')->nullable();
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
        Schema::dropIfExists('template_item_option_dates');
    }
}
