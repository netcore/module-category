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

            $table->unsignedInteger('items_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table
                ->foreign('category_group_id', 'category::categories-category_group_foreign')
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

            $table
                ->foreign('category_id', 'category::category_translations-category_foreign')
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
