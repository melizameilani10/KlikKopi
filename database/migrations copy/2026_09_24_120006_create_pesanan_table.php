<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->foreignId('id_meja')->constrained('meja', 'id_meja')->onDelete('cascade');
            $table->integer('nomor_antrean');
            $table->dateTime('tanggal_pesan')->useCurrent();
            $table->decimal('total_harga', 10, 2)->default(0);
            $table->enum('status_pesanan', ['pending', 'diproses', 'selesai', 'batal'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
