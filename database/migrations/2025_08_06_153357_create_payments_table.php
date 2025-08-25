<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->morphs('paymentable');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('currency', 3)->default(CurrencyAlpha3::Euro);

            $table->enum('status', [
                PaymentStatus::Pending->value,
                PaymentStatus::Success->value,
                PaymentStatus::Failed->value,
            ])->default(PaymentStatus::Pending->value);

            $table->enum('method', [
                PaymentMethod::Transfer,
                PaymentMethod::PaySafeCard,
            ]);

            $table->string('external_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
