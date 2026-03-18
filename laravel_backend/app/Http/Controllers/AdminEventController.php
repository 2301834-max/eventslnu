<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminEventController extends Controller
{
    /**
     * Show all events (for admin)
     */
    public function index(): View
    {
        $events = Event::latest()->paginate(10);
        return view('admin.events.index', ['events' => $events]);
    }

    /**
     * Show create event form
     */
    public function create(): View
    {
        return view('admin.events.create');
    }

    /**
     * Store new event
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1',
            // Map high-level choices in the form to enum values on the model.
            'status' => 'required|in:draft,published,ongoing,completed,cancelled',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('event_image')) {
            $path = $request->file('event_image')->store('events', 'public');
            $validated['event_image'] = $path;
        }

        $validated['created_by'] = auth()->id();

        Event::create($validated);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully!');
    }

    /**
     * Show event details
     */
    public function show(Event $event): View
    {
        $registrations = $event->registrations()->with('user')->paginate(10);
        $attendanceRecords = $event->attendanceRecords()->with('user')->paginate(10);
        
        return view('admin.events.show', [
            'event' => $event,
            'registrations' => $registrations,
            'attendanceRecords' => $attendanceRecords,
        ]);
    }

    /**
     * Show edit event form
     */
    public function edit(Event $event): View
    {
        return view('admin.events.edit', ['event' => $event]);
    }

    /**
     * Update event
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,ongoing,completed,cancelled',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('event_image')) {
            $path = $request->file('event_image')->store('events', 'public');
            $validated['event_image'] = $path;
        }

        $event->update($validated);

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Delete event
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully!');
    }

    /**
     * View all student registrations
     */
    public function registrations(): View
    {
        $registrations = Registration::with('event', 'user', 'approver')
            ->latest()
            ->paginate(15);

        return view('admin.registrations.index', ['registrations' => $registrations]);
    }

    /**
     * Approve a registration
     */
    public function approveRegistration(Registration $registration): RedirectResponse
    {
        $registration->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Registration approved!');
    }

    /**
     * Reject a registration
     */
    public function rejectRegistration(Registration $registration): RedirectResponse
    {
        $registration->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Registration rejected!');
    }

    /**
     * Delete a registration (cleanup for testing / admin control).
     */
    public function destroyRegistration(Registration $registration): RedirectResponse
    {
        $registration->delete();

        return redirect()->back()->with('success', 'Registration deleted.');
    }
}
