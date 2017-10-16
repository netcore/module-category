<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netcore_category__category_translations', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('category_id')->index();
            $table->string('locale', 2)->index();

            $table->string('name');
            $table->string('slug')->index()->unique();

            $table->timestamps();
            $table->unique(['category_id', 'locale'], 'category_locale_unique');
            $table->foreign('category_id')->references('id')->on('netcore_category__categories')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('netcore_category__category_translations');
    }
}
