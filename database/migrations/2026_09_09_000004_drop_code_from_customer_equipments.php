<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // unit_code = satu-satunya kode (pola EntityCode, seperti customer);
        // kolom `code` redundan -> dihapus, unit_code jadi unique.
        Schema::table('customer_equipments', function (Blueprint $table) {
            $table->dropUnique('customer_equipments_code_unique');
            $table->dropColumn('code');
            $table->unique('unit_code');
        });
    }

    public function down(): void
    {
        Schema::table('customer_equipments', function (Blueprint $table) {
            $table->dropUnique('customer_equipments_unit_code_unique');
            $table->string('code', 50)->unique()->after('ulid');
        });
    }
};
