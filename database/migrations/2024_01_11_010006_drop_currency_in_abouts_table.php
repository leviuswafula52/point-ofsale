<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Manually check if the column exists using raw SQL
        $columnExists = DB::select("SHOW COLUMNS FROM abouts LIKE 'currency'");

        if (!empty($columnExists)) {
            Schema::table('abouts', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            if (!Schema::hasColumn('abouts', 'currency')) {
                $table->string('currency')->default('IDR')->after('shop_location');
            }
        });
    }
};
