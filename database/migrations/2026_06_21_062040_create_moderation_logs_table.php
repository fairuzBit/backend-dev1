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
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // 'DIHAPUS', 'SELESAI', dll
            $table->text('reason')->nullable(); // Alasan misal 'Spam/Troll'
            $table->string('target_type'); // 'Review'
            $table->unsignedBigInteger('target_id'); 
            $table->json('details')->nullable(); // Snapshot data yang dimoderasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
    }
};
