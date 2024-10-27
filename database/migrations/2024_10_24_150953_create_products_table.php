<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('status')->nullable();
            $table->decimal('price', 15, 2)->nullable()->default(0.00);
            $table->string('currency', 3)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('hint')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            //$table->unique('sku', 'unique_sku')->whereNotNull('sku');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
