<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Cart\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Cancel an order.
     */
    public function cancelOrder(Request $request, $currentTeam, $id): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
            'shipping_status' => 'cancelled',
        ]);

        DB::table('transaction_payments')
            ->where('transaction_id', $transaction->id)
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

        // If a corresponding sell transaction exists, cancel it too
        $sellTransaction = Transaction::where('type', 'sell')
            ->where('parent_transaction_id', $transaction->id)
            ->first();
        if ($sellTransaction) {
            $sellTransaction->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
            ]);

            DB::table('transaction_payments')
                ->where('transaction_id', $sellTransaction->id)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "❌ Order #{$transaction->ref_no} cancelled successfully.",
        ]);
    }
}
