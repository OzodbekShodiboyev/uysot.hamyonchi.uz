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
        | Shartnoma raqam counter
        | Format: 2024/B1-0001
        |   YIL  / BLOK_KODI - TARTIB(4 raqam)
        |--------------------------------------------------------------
        */
        Schema::create('contract_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->unique(['block_id', 'year']);
        });

        /*
        |--------------------------------------------------------------
        | Shartnomalar
        |--------------------------------------------------------------
        */
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // Raqam
            $table->string('contract_number', 60)->unique();  // 2024/B1-0001
            $table->unsignedInteger('sequence_number');
            $table->unsignedSmallInteger('contract_year');

            // Bog'liqliklar
            $table->foreignId('apartment_id')->constrained()->restrictOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('manager_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('accountant_id')->nullable()->constrained('users')->nullOnDelete();

            // To'lov turi
            $table->enum('payment_type', ['full', 'installment']);

            // Summalar
            $table->decimal('total_price', 15, 2);       // Dastlabki narx
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('final_price', 15, 2);       // Chegirmadan keyin
            $table->decimal('initial_payment', 15, 2)->default(0);  // Boshlang'ich
            $table->decimal('initial_percent', 5, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0); // Qolgan summa
            $table->decimal('paid_amount', 15, 2)->default(0);      // To'langan
            $table->decimal('debt_amount', 15, 2)->default(0);      // Qarz

            // Bo'lib to'lash
            $table->unsignedSmallInteger('installment_months')->default(0);
            $table->decimal('monthly_payment', 15, 2)->default(0);
            $table->date('installment_start_date')->nullable();
            $table->date('installment_end_date')->nullable();

            // Status
            $table->enum('status', [
                'draft',        // Loyiha — hali imzolanmagan
                'active',       // Faol — imzolangan
                'completed',    // To'liq to'langan
                'cancelled',    // Bekor qilingan
                'disputed',     // Muammo bor
            ])->default('draft');

            // Sanalar
            $table->date('signed_date')->nullable();
            $table->date('expected_handover_date')->nullable();
            $table->date('actual_handover_date')->nullable();
            $table->date('cancelled_date')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->string('contract_file')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['contract_year', 'sequence_number']);
            $table->index('status');
            $table->index('payment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_sequences');
    }
};
