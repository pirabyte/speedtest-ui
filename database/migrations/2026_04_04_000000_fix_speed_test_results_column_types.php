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
        Schema::table('speed_test_results', function (Blueprint $table) {
            $table->bigInteger('download_speed')->change();
            $table->bigInteger('upload_speed')->change();
            $table->decimal('ping', 8, 2)->change();
            $table->decimal('packet_loss', 5, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speed_test_results', function (Blueprint $table) {
            $table->string('download_speed')->change();
            $table->string('upload_speed')->change();
            $table->string('ping')->change();
            $table->integer('packet_loss')->default(0)->change();
        });
    }
};
