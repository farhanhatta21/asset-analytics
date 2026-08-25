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
        Schema::create('breakdown_logs', function (Blueprint $table) {

            $table->id();

            // identitas alat
            $table->string('group_alat')->nullable();
            $table->string('nama_alat');

            // waktu breakdown
            $table->dateTime('start_bd')->nullable();
            $table->dateTime('finish_bd')->nullable();

            // durasi breakdown
            $table->float('durasi_bd')->nullable();

            // analisis breakdown
            $table->string('part_group')->nullable();

            $table->text('detail_kerusakan')->nullable();
            $table->text('detail_penyebab')->nullable();
            $table->text('detail_tindakan')->nullable();

            // kendala maintenance
            $table->text('kendala')->nullable();

            // hasil akhir
            $table->text('keterangan')->nullable();

            // periode upload
            $table->string('periode')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breakdown_logs');
    }
};
