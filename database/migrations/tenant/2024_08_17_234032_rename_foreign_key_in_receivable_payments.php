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
        // Skip foreign key changes if using mysql
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('receivable_payments', function (Blueprint $table) {
            $table->dropForeign('debt_payments_debt_id_foreign');
            $table->foreign('receivable_id')->references('id')->on('receivables');

            $table->dropForeign('debt_payments_payment_method_id_foreign');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');

            $table->dropForeign('debt_payments_user_id_foreign');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip foreign key changes if using mysql
        if (DB::getDriverName() === 'mysql') {
            return;
        }

        Schema::table('receivable_payments', function (Blueprint $table) {
            $table->dropForeign(['receivable_id']);
            $table->foreign('receivable_id', 'debt_payments_debt_id_foreign')->references('id')->on('receivables');

            $table->dropForeign(['payment_method_id']);
            $table->foreign('payment_method_id', 'debt_payments_payment_method_id_foreign')->references('id')->on('payment_methods');

            $table->dropForeign(['user_id']);
            $table->foreign('user_id', 'debt_payments_user_id_foreign')->references('id')->on('users');
        });
    }
};
//             'email' => 'The provided credentials do not match our records.',
//         ]);