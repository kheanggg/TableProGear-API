<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('user_id');     // unsigned tinyint
            $table->unsignedTinyInteger('product_id');  // unsigned tinyint

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
