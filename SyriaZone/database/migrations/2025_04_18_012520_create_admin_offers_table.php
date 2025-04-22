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
        Schema::create('admin_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offerable_id');
            $table->string('offerable_type');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('active');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->decimal('discount_percentage', 5, 2);
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['offerable_id', 'offerable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_offers');
    }
};
