<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Category milik subgroup (Group -> Subgroup -> Category -> Item).
        Schema::table('equipment_categories', function (Blueprint $table) {
            $table->foreignId('subgroup_id')->nullable()->after('name')
                ->constrained('equipment_subgroups')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('subgroup_id');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_categories', function (Blueprint $table) {
            $table->dropForeign(['subgroup_id']);
            $table->dropColumn(['subgroup_id', 'sort_order']);
        });
    }
};
