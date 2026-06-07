<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('phone_extra', 20)->nullable();

            $table->enum('source', [
                'instagram', 'telegram', 'phone', 'referral', 'office', 'banner', 'other'
            ])->default('office');

            $table->enum('status', [
                'new', 'contacted', 'consultation', 'considering', 'hot', 'lost', 'converted'
            ])->default('new');

            $table->foreignId('interested_block_id')->nullable()->constrained('blocks')->nullOnDelete();
            $table->integer('rooms')->nullable();
            $table->decimal('budget', 18, 2)->nullable();

            $table->text('notes')->nullable();
            $table->date('next_follow_up')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');

            $table->foreignId('converted_client_id')->nullable()->constrained('clients')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'next_follow_up']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
