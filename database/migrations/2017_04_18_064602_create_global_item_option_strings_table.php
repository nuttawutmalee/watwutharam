<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalItemOptionStringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('global_item_option_strings');

        Schema::create('global_item_option_strings', function (Blueprint $table) {
            $table->uuid('global_item_option_id');
            $table->primary('global_item_option_id');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->longText('option_value')->nullable();
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
        Schema::dropIfExists('global_item_option_strings');
    }
}
