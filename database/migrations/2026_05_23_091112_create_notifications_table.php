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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            // 1. Kolom untuk menghubungkan notifikasi ke ID User yang login
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            // 2. Kolom untuk judul notifikasi (misal: "Pesanan Masuk", "Pembayaran Sukses")
            $table->string('title'); 
            
            // 3. Kolom untuk isi detail notifikasinya
            $table->text('message'); 
            
            // 4. Kolom status (false = belum dibaca, true = sudah dibaca)
            $table->boolean('is_read')->default(false); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};