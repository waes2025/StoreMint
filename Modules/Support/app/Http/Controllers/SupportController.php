<?php

namespace Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Support\Models\SupportTicket;
use Modules\Support\Models\SupportMessage;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    /**
     * Store a new support ticket from a customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'orderId' => 'nullable|string|max:100',
            'message' => 'required|string|min:5',
        ]);

        $user = $request->user();
        $businessId = currentBusinessId() ?: ($user?->business_id ?? 1);

        // Generate unique ticket number
        do {
            $ticketNumber = 'TKT-' . rand(10000, 99999);
        } while (SupportTicket::where('ticket_number', $ticketNumber)->exists());

        DB::transaction(function () use ($request, $user, $businessId, $ticketNumber) {
            $ticket = SupportTicket::create([
                'ticket_number' => $ticketNumber,
                'business_id' => $businessId,
                'user_id' => $user->id,
                'order_id' => $request->input('orderId'),
                'category' => $request->input('category'),
                'status' => 'Open',
            ]);

            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->input('message'),
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => "💬 Support ticket {$ticketNumber} has been submitted!",
        ]);
    }

    /**
     * Reply to a ticket as a customer.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        $user = $request->user();

        // Ensure user owns this ticket
        if ($ticket->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($request, $user, $ticket) {
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->input('message'),
            ]);

            // Re-open ticket if it was resolved/closed
            $ticket->status = 'Open';
            $ticket->touch();
            $ticket->save();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => '💬 Reply posted successfully!',
        ]);
    }
}
