<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Equipment milik customer (My Equipment).
        Schema::create('customer_equipments', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('code', 50)->unique();
            $table->string('unit_code', 50);
            $table->string('unit_name', 190);
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('serial_no', 100)->nullable();
            $table->string('location', 150)->nullable();
            $table->unsignedSmallInteger('procurement_year')->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();
            $table->date('cert_expired')->nullable();
            $table->string('status', 20)->default('active'); // active|inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
        });

        // id mulai 10213 (keputusan user) — code = EntityCode::encode(id, 4).
        DB::statement('ALTER TABLE customer_equipments AUTO_INCREMENT = 10213');
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_equipments');
    }
};
