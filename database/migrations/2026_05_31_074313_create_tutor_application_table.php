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
        Schema::create('tutor_application', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('new_semester')->nullable()->comment('For semester upgrades');
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->string('grade')->nullable();
            $table->json('transcript_files')->nullable();
            $table->json('portfolio_urls')->nullable();
            $table->json('certificate_files')->nullable();
            $table->enum('status',['pending','approved','rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_application');
    }
};
