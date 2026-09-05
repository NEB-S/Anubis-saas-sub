<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalTicketController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $tickets = $customer->supportTickets()->latest()->get();

        return view('portal.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('portal.tickets.create');
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::create([
            'customer_id' => $customer->id,
            'subject' => $data['subject'],
            'status' => 'open',
        ]);

        $ticket->messages()->create([
            'sender' => 'customer',
            'message' => $data['message'],
        ]);

        return redirect()->route('portal.tickets.show', $ticket)->with('success', 'Ticket opened.');
    }

    public function show(SupportTicket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($ticket->customer_id === $customer->id, 403);

        $ticket->load('messages');

        return view('portal.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($ticket->customer_id === $customer->id, 403);

        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $ticket->messages()->create(['sender' => 'customer', 'message' => $data['message']]);

        // A customer replying re-opens a ticket you'd already closed —
        // otherwise their follow-up would sit in a "closed" ticket you might
        // not think to check again.
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply sent.');
    }
}
