<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 10)->default('EGP');
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('discounted_price', 15, 2)->nullable();
            $table->enum('type', ['sale', 'rent'])->default('sale');
            $table->enum('purpose', ['residential', 'commercial'])->default('residential');
            $table->string('property_type');
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->integer('living_rooms')->default(0);
            $table->integer('kitchens')->default(0);
            $table->integer('balconies')->default(0);
            $table->decimal('area_total', 10, 2);
            $table->json('features')->nullable();
            $table->json('tags')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();
            $table->enum('furnishing', ['unfurnished', 'semi-furnished', 'furnished'])->default('furnished');
            $table->enum('status', ['available', 'sold', 'rented'])->default('available');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('agency_id')->nullable();
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('set null');

            $table->index('price');
            $table->index('type');
            $table->index('status');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
