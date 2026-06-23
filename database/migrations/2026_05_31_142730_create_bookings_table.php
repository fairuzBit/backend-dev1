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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Relasi utama
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained('users')->cascadeOnDelete();
            
            // Tanggal pemesanan (Kita asumsikan pesan banyak slot tapi di hari yang sama)
            $table->date('booking_date');
            
            // Data harga (DP dihapus)
            $table->decimal('total_price', 10, 2);
            $table->decimal('service_fee', 10, 2)->default(15000); // Biaya Layanan Tetap
            $table->decimal('grand_total', 10, 2); // Harga Total + Layanan
            // Status alur bisnis
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable(); // bank_transfer, e_wallet, cash
            $table->string('payment_code')->nullable(); // Nomor VA atau kode bayar
            $table->timestamp('payment_expired_at')->nullable(); // Waktu kadaluarsa pembayaran
            
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
