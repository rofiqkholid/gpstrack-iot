<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')->constrained()->onDelete('cascade');
            $table->string('component_name');
            $table->text('description')->nullable();
            $table->unsignedInteger('cost')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_record_items');
    }
};
