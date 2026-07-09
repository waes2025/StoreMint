<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('transaction_payments')) {
            Schema::create('transaction_payments', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('transaction_payments', 'transaction_id')) {
                    $table->unsignedInteger('transaction_id');
                }

                if (! Schema::hasColumn('transaction_payments', 'business_id')) {
                    $table->integer('business_id')->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'is_return')) {
                    $table->tinyInteger('is_return')->default(0);
                }

                if (! Schema::hasColumn('transaction_payments', 'amount')) {
                    $table->decimal('amount', 20, 2)->default(0);
                }

                if (! Schema::hasColumn('transaction_payments', 'method')) {
                    $table->string('method', 50)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'payment_type')) {
                    $table->string('payment_type', 50)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'transaction_no')) {
                    $table->string('transaction_no', 191)->nullable();
                }

                // Card details
                if (! Schema::hasColumn('transaction_payments', 'card_transaction_number')) {
                    $table->string('card_transaction_number', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'card_number')) {
                    $table->string('card_number', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'card_type')) {
                    $table->string('card_type', 50)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'card_holder_name')) {
                    $table->string('card_holder_name', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'card_month')) {
                    $table->string('card_month', 10)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'card_year')) {
                    $table->string('card_year', 10)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'card_security')) {
                    $table->string('card_security', 10)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'cheque_number')) {
                    $table->string('cheque_number', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'bank_account_number')) {
                    $table->string('bank_account_number', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'paid_on')) {
                    $table->dateTime('paid_on')->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'created_by')) {
                    $table->integer('created_by')->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'gateway')) {
                    $table->string('gateway', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'is_advance')) {
                    $table->tinyInteger('is_advance')->default(0);
                }

                if (! Schema::hasColumn('transaction_payments', 'is_rebate')) {
                    $table->tinyInteger('is_rebate')->default(0);
                }

                if (! Schema::hasColumn('transaction_payments', 'payment_for')) {
                    $table->integer('payment_for')->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'parent_id')) {
                    $table->integer('parent_id')->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'note')) {
                    $table->text('note')->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'document')) {
                    $table->string('document', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'payment_ref_no')) {
                    $table->string('payment_ref_no', 191)->nullable();
                }

                if (! Schema::hasColumn('transaction_payments', 'account_id')) {
                    $table->integer('account_id')->nullable();
                }

                $table->timestamps();

                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
