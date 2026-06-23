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
        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('ipk', 3, 2)->nullable()->comment('Calculated via OCR');
            $table->text('bio')->nullable();
            $table->json('portfolio_urls')->nullable();
            $table->json('certificate_files')->nullable();
            $table->json('skills')->nullable();
            $table->float('rating_avg')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('current_semester')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price',10,2)->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutors');
    }
};
