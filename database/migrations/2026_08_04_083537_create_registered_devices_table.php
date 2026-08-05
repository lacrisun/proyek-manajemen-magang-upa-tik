<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registered_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')
                ->constrained('participants')
                ->cascadeOnDelete();
            $table->string('device_model')->nullable();      // model HP, misal "SM-A536E"
            $table->string('device_fingerprint')->nullable(); // hash dari FingerprintJS
            $table->string('user_agent')->nullable();         // simpan mentah untuk log/audit
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);      // buat nonaktifkan tanpa hapus data
            $table->timestamps();

            $table->unique('participant_id'); // 1 participant = 1 device aktif
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registered_devices');
    }
};
