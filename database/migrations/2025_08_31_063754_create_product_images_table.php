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
        Schema::create('product_images', function (Blueprint $table) {
            $table->unsignedSmallInteger('product_id');
            $table->string('image_url');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Foreign key
            $table->foreign('product_id')
                  ->references('product_id')->on('products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
