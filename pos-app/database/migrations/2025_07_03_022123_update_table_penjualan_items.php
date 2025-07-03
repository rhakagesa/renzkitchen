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
        //
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan_items');
        Schema::enableForeignKeyConstraints();
        Schema::create('penjualan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pendapatan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('produk_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('qty');
            $table->decimal('harga', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->softDeletes();
            $table->timestamps();
        });     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
