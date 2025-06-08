<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            if (DB::getDriverName() !== 'mysql') {
                // Safely drop foreign keys only if not using SQLite
                $table->dropForeign(['member_id']);
                $table->dropForeign(['selling_id']);
            }

            // Re-apply foreign keys (Laravel will skip this silently on SQLite if they exist)
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('selling_id')->references('id')->on('sellings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            if (DB::getDriverName() !== 'mysql') {
                $table->dropForeign(['member_id']);
                $table->dropForeign(['selling_id']);
            }

            // Re-apply original foreign key names (useful for MySQL, harmless for SQLite)
            $table->foreign('member_id', 'debts_member_id_foreign')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('selling_id', 'debts_selling_id_foreign')->references('id')->on('sellings')->onDelete('cascade');
        });
    }
};          
