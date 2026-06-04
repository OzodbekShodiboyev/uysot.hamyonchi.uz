<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------
        | Oylik to'lov jadvali (bo'lib to'lash uchun)
        |--------------------------------------------------------------
        */
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('payment_number');   // 1, 2, 3...
            $table->unsignedSmallInteger('total_payments');   // Jami oylar soni
            $table->decimal('amount', 15, 2);                 // Oylik to'lov summasi
            $table->decimal('penalty_rate', 5, 2)->default(0); // Jarima foizi
            $table->date('due_date');                         // To'lov muddati
            $table->enum('status', [
                'pending',   // Kutilmoqda
                'paid',      // To'langan
                'partial',   // Qisman to'langan
                'overdue',   // Muddati o'tgan
                'cancelled', // Bekor qilingan
            ])->default('pending');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        /*
        |--------------------------------------------------------------
        | Kvitansiya raqam counter
        | Format: KV-2024-000001
        |--------------------------------------------------------------
        */
        Schema::create('payment_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedInteger('last_sequence')->default(0);
        });

        /*
        |--------------------------------------------------------------
        | Haqiqiy to'lovlar (kvitansiyalar)
        |--------------------------------------------------------------
        */
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();

            $table->string('receipt_number', 60)->unique(); // KV-2024-000001
            $table->unsignedInteger('receipt_sequence');

            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'bank', 'card', 'online'])->default('cash');
            $table->string('bank_reference')->nullable();
            $table->date('payment_date');
            $table->enum('type', [
                'initial',      // Boshlang'ich to'lov
                'installment',  // Oylik bo'lib to'lash
                'full',         // To'liq to'lov
                'penalty',      // Jarima
                'extra',        // Qo'shimcha
                'refund',       // Qaytarish
            ]);
            $table->string('receipt_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['contract_id', 'payment_date']);
            $table->index('payment_method');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_sequences');
        Schema::dropIfExists('payment_schedules');
    }
};
