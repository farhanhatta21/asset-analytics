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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            $table->string('nama_alat');
            $table->string('group_alat')->nullable();

            $table->float('availability')->nullable();
            $table->float('mtbf')->nullable();
            $table->float('mttr')->nullable();
            $table->float('utilisation')->nullable();

            $table->float('produksi')->nullable();
            $table->float('bbm')->nullable();
            $table->float('listrik')->nullable();

            $table->text('remark')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};