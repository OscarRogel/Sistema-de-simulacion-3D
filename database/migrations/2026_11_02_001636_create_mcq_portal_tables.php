<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Usuarios del Portal MCQ
        Schema::create('mcq_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('name');
            $table->enum('role', ['ADMIN', 'USER'])->default('USER');
            $table->timestamps();
        });

        // 2. Temporadas (SP26, FA26, SP27, FA27, etc.)
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->timestamps();
        });

        // 3. Marcas (Patagonia, TNF, Kühl, Engelbert Strauss, etc.)
        Schema::create('brands', function (Blueprint $table) {
            $table->string('id')->primary(); // ej. 'patagonia'
            $table->string('name');
            $table->string('color', 20);
            $table->string('badge', 10);
            $table->timestamps();
        });

        // 4. Catálogo de Cajas Master
        Schema::create('master_boxes', function (Blueprint $table) {
            $table->string('id')->primary(); // ej. 'box-ptg-1'
            $table->string('brand_id');
            $table->string('name');
            $table->decimal('l', 8, 2);
            $table->decimal('w', 8, 2);
            $table->decimal('h', 8, 2);
            $table->decimal('tare', 8, 2);
            $table->decimal('max_kg', 8, 2);
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
        });

        // 5. Estilos / Registros MCQ
        Schema::create('mcq_tests', function (Blueprint $table) {
            $table->string('id')->primary(); // ej. 'MCQ-2026-101'
            $table->string('season', 20);
            $table->string('brand_id');
            $table->string('style_code');
            $table->string('category');
            $table->decimal('garment_l', 8, 2);
            $table->decimal('garment_w', 8, 2);
            $table->decimal('garment_h', 8, 2);
            $table->decimal('compression_pct', 5, 2)->default(0);
            $table->boolean('lock_flat')->default(false);
            $table->decimal('unit_weight_grams', 8, 2);
            $table->integer('order_qty');
            $table->string('box_name');
            $table->decimal('box_l', 8, 2);
            $table->decimal('box_w', 8, 2);
            $table->decimal('box_h', 8, 2);
            $table->decimal('box_tare_grams', 8, 2);
            $table->decimal('max_weight_limit_kg', 8, 2);
            $table->string('created_by')->default('Sistema');
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcq_tests');
        Schema::dropIfExists('master_boxes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('mcq_users');
    }
};