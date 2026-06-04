<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone');
            $table->string('phone_extra')->nullable();
            $table->string('passport_series', 20)->nullable();
            $table->date('passport_issued_date')->nullable();
            $table->string('passport_issued_by')->nullable();
            $table->string('pinfl', 14)->nullable()->unique();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
