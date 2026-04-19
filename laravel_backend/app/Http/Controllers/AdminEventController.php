<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminEventController extends Controller
{
    /**
     * Show all events (for admin)
     */
    public function index(): View
    {
        $events = Event::withCount('registrations')
            ->latest()
            ->paginate(10);

        $summary = [
            'total' => Event::count(),
            'upcoming' => Event::where('start_date', '>=', now())->count(),
            'active' => Event::whereIn('status', ['published', 'ongoing'])->count(),
            'completed' => Event::where('status', 'completed')->count(),
        ];

        return view('admin.events.index', [
            'events' => $events,
            'summary' => $summary,
        ]);
    }

    /**
     * Show create event form
     */
    public function create(): View
    {
        return view('admin.events.create', [
            'minimumStartDate' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    /**
     * Store new event
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEventData($request);

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
        return view('admin.events.edit', [
            'event' => $event,
            'minimumStartDate' => $event->start_date->greaterThan(now())
                ? now()->format('Y-m-d\TH:i')
                : $event->start_date->format('Y-m-d\TH:i'),
        ]);
    }

    /**
     * Update event
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $this->validateEventData($request, $event);

        if ($request->hasFile('event_image')) {
            if ($event->event_image) {
                Storage::disk('public')->delete($event->event_image);
            }
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

    private function validateEventData(Request $request, ?Event $event = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,ongoing,completed,cancelled',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'end_date.after_or_equal' => 'The event end date must be after the start date.',
            'event_image.mimes' => 'Event posters must be uploaded as JPG or PNG files only.',
            'event_image.max' => 'Event posters must not be larger than 5MB.',
        ]);

        $submittedStart = Carbon::parse($validated['start_date'])->seconds(0);
        $currentMinute = now()->seconds(0);

        if ($event === null && $submittedStart->lt($currentMinute)) {
            throw ValidationException::withMessages([
                'start_date' => 'You cannot register an event with a start date and time that has already passed.',
            ]);
        }

        if ($event !== null) {
            $originalStart = $event->start_date->copy()->seconds(0);
            $startWasChanged = $submittedStart->format('Y-m-d H:i') !== $originalStart->format('Y-m-d H:i');

            if ($startWasChanged && $submittedStart->lt($currentMinute)) {
                throw ValidationException::withMessages([
                    'start_date' => 'You cannot move an event start date to a time that has already passed.',
                ]);
            }
        }

        return $validated;
    }
}
