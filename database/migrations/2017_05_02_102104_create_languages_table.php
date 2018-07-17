<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('languages');

        Schema::create('languages', function (Blueprint $table) {
            $table->string('code');
            $table->primary('code');
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('name')->unique();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('hreflang')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->string('locale')->nullable();
            /** @noinspection PhpUndefinedMethodInspection */
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('languages');
    }
}
