<?php

use App\Models\Product;
use App\Models\vendor;
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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(vendor::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('status')->default('active'); // Status of the discount (e.g., active, inactive)
            $table->string('fromtime'); // Start time of the discount
            $table->string('totime'); // End time of the discount
            $table->decimal('value', 10, 2); // Discount value (e.g., percentage or fixed amount)
            $table->timestamps(); // Created at and updated at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
