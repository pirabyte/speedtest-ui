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
        Schema::create('speed_test_results', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id');
            $table->string('download_speed');
            $table->string('upload_speed');
            $table->timestamp('timestamp');
            $table->string('ping');
            $table->string('user_isp');
            $table->string('user_ip');
            $table->integer('packet_loss')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speed_test_results');
    }
};
