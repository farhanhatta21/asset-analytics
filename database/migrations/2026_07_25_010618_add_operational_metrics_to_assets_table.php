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
        Schema::table('assets', function (Blueprint $table) {

            $table->decimal('mttrc', 10, 2)
                ->nullable()
                ->after('mtbf');

            $table->decimal('mttrp', 10, 2)
                ->nullable()
                ->after('mttrc');

            $table->decimal('accident', 10, 2)
                ->nullable()
                ->after('mttrp');

            $table->decimal('breakdown_duration', 10, 2)
                ->nullable()
                ->after('accident');

            $table->decimal('total_breakdown', 10, 2)
                ->nullable()
                ->after('breakdown_duration');

            $table->integer('number_of_breakdowns')
                ->nullable()
                ->after('total_breakdown');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {

            $table->dropColumn([
                'mttrc',
                // mean time to recover
                'mttrp',
                // mean time to repair
                'accident',
                // durasi accident
                'breakdown_duration',
                // durasi kategori breakdown
                'total_breakdown', 
                // total breakdown duration
                'number_of_breakdowns',
                // jumlah kejadian breakdown
            ]);

        });
    }
};
