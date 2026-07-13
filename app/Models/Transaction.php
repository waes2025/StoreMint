<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'business_id',
        'location_id',
        'contact_id',
        'created_by',
        'type',
        'status',
        'payment_status',
        'invoice_no',
        'ref_no',
        'transaction_date',
        'total_before_tax',
        'tax_id',
        'tax_amount',
        'discount_type',
        'discount_amount',
        'coupon_id',
        'shipping_charges',
        'final_total',
        'shipping_address',
        'parent_transaction_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class, 'transaction_id');
    }

    /**
     * Check if the transaction is a completed & paid sales order,
     * and automatically generate a new 'sell' transaction if not already done.
     */
    public static function checkAndGenerateSell($transactionId)
    {
        $transaction = self::find($transactionId);
        if (!$transaction) {
            return;
        }

        if (in_array($transaction->type, ['sales_order', 'sale_order']) &&
            $transaction->status === 'completed' &&
            $transaction->payment_status === 'paid'
        ) {
            // Check if a 'sell' transaction already exists for this sales order
            $exists = self::where('type', 'sell')
                ->where('parent_transaction_id', $transaction->id)
                ->exists();

            if (!$exists) {
                // Generate new 'sell' transaction
                $invNo = 'INV-SELL-' . rand(1000, 9999) . '-' . rand(100, 999);
                $refNo = 'SELL-' . rand(100000, 999999);

                $sellId = \Illuminate\Support\Facades\DB::table('transactions')->insertGetId([
                    'business_id' => $transaction->business_id,
                    'location_id' => $transaction->location_id,
                    'contact_id' => $transaction->contact_id,
                    'created_by' => $transaction->created_by,
                    'type' => 'sell',
                    'status' => 'final',
                    'payment_status' => 'paid',
                    'invoice_no' => $invNo,
                    'ref_no' => $refNo,
                    'parent_transaction_id' => $transaction->id,
                    'transaction_date' => now(),
                    'total_before_tax' => $transaction->total_before_tax,
                    'tax_id' => $transaction->tax_id,
                    'tax_amount' => $transaction->tax_amount,
                    'discount_type' => $transaction->discount_type,
                    'discount_amount' => $transaction->discount_amount,
                    'coupon_id' => $transaction->coupon_id,
                    'shipping_charges' => $transaction->shipping_charges,
                    'shipping_address' => $transaction->shipping_address,
                    'final_total' => $transaction->final_total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Copy transaction payments if any exist
                $payments = \Illuminate\Support\Facades\DB::table('transaction_payments')
                    ->where('transaction_id', $transaction->id)
                    ->get();

                foreach ($payments as $payment) {
                    \Illuminate\Support\Facades\DB::table('transaction_payments')->insert([
                        'transaction_id' => $sellId,
                        'business_id' => $payment->business_id,
                        'amount' => $payment->amount,
                        'method' => $payment->method,
                        'payment_type' => $payment->payment_type,
                        'gateway' => $payment->gateway,
                        'paid_on' => $payment->paid_on,
                        'created_by' => $payment->created_by,
                        'status' => $payment->status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Copy transaction sell lines
                $sellLines = \Illuminate\Support\Facades\DB::table('transaction_sell_lines')
                    ->where('transaction_id', $transaction->id)
                    ->get();

                foreach ($sellLines as $line) {
                    \Illuminate\Support\Facades\DB::table('transaction_sell_lines')->insert([
                        'transaction_id' => $sellId,
                        'product_id' => $line->product_id,
                        'variation_id' => $line->variation_id,
                        'quantity' => $line->quantity,
                        'mfg_waste_percent' => $line->mfg_waste_percent,
                        'mfg_ingredient_group_id' => $line->mfg_ingredient_group_id,
                        'secondary_unit_quantity' => $line->secondary_unit_quantity,
                        'quantity_returned' => $line->quantity_returned,
                        'unit_price_before_discount' => $line->unit_price_before_discount,
                        'unit_price' => $line->unit_price,
                        'line_discount_type' => $line->line_discount_type,
                        'line_discount_amount' => $line->line_discount_amount,
                        'unit_price_inc_tax' => $line->unit_price_inc_tax,
                        'item_tax' => $line->item_tax,
                        'tax_id' => $line->tax_id,
                        'discount_id' => $line->discount_id,
                        'lot_no_line_id' => $line->lot_no_line_id,
                        'sell_line_note' => $line->sell_line_note,
                        'woocommerce_line_items_id' => $line->woocommerce_line_items_id,
                        'so_line_id' => $line->id,
                        'so_quantity_invoiced' => $line->quantity,
                        'res_service_staff_id' => $line->res_service_staff_id,
                        'res_line_order_status' => $line->res_line_order_status,
                        'parent_sell_line_id' => $line->parent_sell_line_id,
                        'children_type' => $line->children_type,
                        'sub_unit_id' => $line->sub_unit_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
