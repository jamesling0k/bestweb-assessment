<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['enabled', 'deleted_at', 'id']);
            $table->index(['category_id', 'deleted_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
