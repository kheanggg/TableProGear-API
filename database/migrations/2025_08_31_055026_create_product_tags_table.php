<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_tags', function (Blueprint $table) {
            $table->unsignedSmallInteger('product_id');
            $table->unsignedSmallInteger('tag_id');

            // Foreign keys
            $table->foreign('product_id')
                ->references('product_id')->on('products')
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('tag_id')->on('tags')
                ->onDelete('cascade');

            // Prevent duplicates
            $table->unique(['product_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tags');
    }
};
