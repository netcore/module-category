<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netcore_category__categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_group_id');

            // Nested set columns.
            NestedSet::columns($table);

            // Simple icon. (select2)
            $table->string('icon')->nullable();

            // Stapler fields. (image icon)
            $table->string('file_icon_file_name')->nullable();
            $table->integer('file_icon_file_size')->nullable();
            $table->string('file_icon_content_type')->nullable();
            $table->timestamp('file_icon_updated_at')->nullable();

            $table->unsignedInteger('items_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_group_id', 'category_category_group_foreign')
                  ->references('id')
                  ->on('netcore_category__category_groups')
                  ->onDelete('CASCADE');
        });

        Schema::create('netcore_category__category_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('locale', 2)->index();
            $table->string('name');
            $table->string('slug')->index()->unique();
            $table->string('full_slug')->nullable()->index();

            $table->timestamps();

            $table->unique(['category_id', 'locale'], 'category_locale_unique');
            $table->foreign('category_id', 'category_translation_category_foreign')
                  ->references('id')
                  ->on('netcore_category__categories')
                  ->onDelete('CASCADE');
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
        Schema::dropIfExists('netcore_category__categories');
    }
}
