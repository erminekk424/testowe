<?php

use App\Enums\ProductType;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->string('image');

            $table->jsonb('attributes')->nullable();

            $table->enum('type', [
                ProductType::Account->value,
                ProductType::Currency->value,
                ProductType::CustomItem->value,
                //                ProductType::RequestedBoosting->value,
                //                ProductType::TopUp->value,
                //                ProductType::GiftCard->value,
            ]);

            $table->integer('price');
            $table->string('currency', 3)->default(CurrencyAlpha3::Euro);

            $table->integer('min_quantity');
            $table->integer('max_quantity');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
