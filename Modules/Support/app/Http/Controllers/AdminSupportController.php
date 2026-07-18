<?php

namespace Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Support\Models\SupportTicket;
use Modules\Support\Models\SupportMessage;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminSupportController extends Controller
{
    /**
     * Display a listing of support tickets for the admin.
     */
    public function index(Request $request)
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = currentBusinessId() ?: ($request->user()->business_id ?? 1);

        $tickets = SupportTicket::with(['user', 'messages.user'])
            ->where('business_id', $businessId)
            ->latest('updated_at')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->ticket_number,
                    'db_id' => $ticket->id,
                    'category' => $ticket->category,
                    'orderId' => $ticket->order_id ?: 'None',
                    'status' => $ticket->status,
                    'date' => $ticket->created_at->format('M d, Y'),
                    'customer' => [
                        'name' => trim(($ticket->user->first_name ?? '') . ' ' . ($ticket->user->last_name ?? '')),
                        'email' => $ticket->user->email ?? '-',
                        'username' => $ticket->user->username ?? '-',
                    ],
                    'messages' => $ticket->messages->map(fn($m) => [
                        'id' => $m->id,
                        'message' => $m->message,
                        'sender' => trim(($m->user->first_name ?? '') . ' ' . ($m->user->last_name ?? '')),
                        'is_admin' => $m->user ? ($m->user->user_type === 'admin') : false,
                        'date' => $m->created_at->format('M d, Y H:i'),
                    ])
                ];
            });

        return Inertia::render('Support::Admin/Index', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Reply to a ticket as an admin/agent.
     */
    public function reply(Request $request, $currentTeam, SupportTicket $ticket)
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($request, $user, $ticket) {
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->input('message'),
            ]);

            // If ticket was Open, change status to In Progress
            if ($ticket->status === 'Open') {
                $ticket->status = 'In Progress';
            }
            $ticket->touch();
            $ticket->save();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => '💬 Reply posted successfully!',
        ]);
    }

    /**
     * Update the status of a support ticket.
     */
    public function updateStatus(Request $request, $currentTeam, SupportTicket $ticket)
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'status' => 'required|string|in:Open,In Progress,Resolved,Closed',
        ]);

        $ticket->status = $request->input('status');
        $ticket->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "🏷️ Ticket status updated to {$ticket->status}!",
        ]);
    }
}
