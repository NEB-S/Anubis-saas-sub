<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('customer')->latest()->paginate(25);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load('messages', 'customer');

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $ticket->messages()->create(['sender' => 'admin', 'message' => $data['message']]);

        return back()->with('success', 'Reply sent.');
    }

    public function close(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'Ticket closed.');
    }

    public function reopen(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'open']);

        return back()->with('success', 'Ticket reopened.');
    }
}
