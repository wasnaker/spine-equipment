<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Group — level tertinggi (Group -> Subgroup -> Item).
        Schema::create('equipment_groups', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Subgroup — anak group.
        Schema::create('equipment_subgroups', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->foreignId('group_id')->constrained('equipment_groups')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['group_id', 'code']);
        });

        // Category — atribut item (bukan hierarki).
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Item katalog.
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('rate', 15, 2)->default(0);
            $table->string('unit', 30)->nullable();
            $table->foreignId('subgroup_id')->constrained('equipment_subgroups')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('equipment_categories')->nullOnDelete();
            $table->string('status', 20)->default('draft'); // draft|active|inactive|expired
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subgroup_id', 'status']);
        });

        // Dokumen kirim-customer (ala quotation).
        Schema::create('equipment_documents', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('number', 50);
            $table->string('prefix', 20)->default('EQ');
            $table->date('date');
            $table->date('expirydate')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('surveyor_id')->nullable()->constrained('surveyors')->nullOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft|sent|accepted|declined|expired
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Item dalam dokumen.
        Schema::create('equipment_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('equipment_documents')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('rate', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_document_items');
        Schema::dropIfExists('equipment_documents');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('equipment_categories');
        Schema::dropIfExists('equipment_subgroups');
        Schema::dropIfExists('equipment_groups');
    }
};
