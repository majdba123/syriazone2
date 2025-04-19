<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Rating;
use App\Models\User;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answer__ratings', function (Blueprint $table) {
            $table->foreignIdFor(Rating::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer__ratings');
    }
};
