<?php

use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained();

            $table->integer('amount');
            $table->string('currency', 3)->default(CurrencyAlpha3::Euro);

            // Notice: this casts to OrderStatus enum in Order model.
            $table->enum('status', [
                OrderStatus::Pending->value,
                OrderStatus::Processing->value,
                OrderStatus::Completed->value,
            ])->default(OrderStatus::Pending->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
