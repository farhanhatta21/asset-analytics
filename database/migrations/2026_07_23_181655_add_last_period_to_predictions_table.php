<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastPeriodToPredictionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {

            $table
                ->string('last_period', 7)
                ->after('nama_alat');

        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {

            $table->dropColumn('last_period');

        });
    }
}