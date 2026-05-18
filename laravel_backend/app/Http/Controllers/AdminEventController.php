<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
            'organizations' => $this->organizationOptions(),
        ]);
    }

    /**
     * Store new event
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizePosterUpload($request);

        $validated = $this->validateEventData($request);
        unset($validated['poster']);

        if ($this->hasPosterUpload($request)) {
            $posterPath = $this->storePoster($request);
            $validated['event_image'] = $posterPath;
        }

        $validated['created_by'] = auth()->id();

        $event = Event::create($validated);

        ActivityLog::record('event.created', 'Created event: '.$event->title, $event, auth()->user(), [
            'event_title' => $event->title,
            'organization' => $event->organization,
        ]);

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
    public function edit(Event $event): View|RedirectResponse
    {
        if (! $event->allowsAdminChanges()) {
            return redirect()
                ->route('admin.events.show', $event)
                ->withErrors(['event' => 'This event can only be viewed because its status is '.ucfirst($event->status).'.']);
        }

        return view('admin.events.edit', [
            'event' => $event,
            'minimumStartDate' => $event->start_date->greaterThan(now())
                ? now()->format('Y-m-d\TH:i')
                : $event->start_date->format('Y-m-d\TH:i'),
            'organizations' => $this->organizationOptions(),
        ]);
    }

    /**
     * Update event
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        if (! $event->allowsAdminChanges()) {
            return redirect()
                ->route('admin.events.show', $event)
                ->withErrors(['event' => 'This event can only be viewed because its status is '.ucfirst($event->status).'.']);
        }

        $this->normalizePosterUpload($request);

        $validated = $this->validateEventData($request, $event);
        unset($validated['poster']);

        if ($this->hasPosterUpload($request)) {
            $newPosterPath = $this->storePoster($request);

            $this->deletePoster($event);

            $validated['event_image'] = $newPosterPath;
        }

        $event->update($validated);
        $event->refresh();

        ActivityLog::record('event.updated', 'Updated event: '.$event->title, $event, auth()->user(), [
            'event_title' => $event->title,
            'organization' => $event->organization,
        ]);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', $event->poster
                ? 'Event updated successfully. Poster image saved.'
                : 'Event updated successfully, but no poster image was uploaded.');
    }

    public function updatePoster(Request $request, Event $event): JsonResponse
    {
        if (! $event->allowsAdminChanges()) {
            return response()->json([
                'message' => 'This event poster can no longer be changed.',
            ], 422);
        }

        $this->normalizePosterUpload($request);

        $request->validate([
            'poster' => 'required_without:event_image|image|mimes:jpg,jpeg,png,webp|max:20480',
            'event_image' => 'required_without:poster|image|mimes:jpg,jpeg,png,webp|max:20480',
        ], [
            'poster.required_without' => 'Please choose an event poster image.',
            'poster.image' => 'The poster must be a valid image file.',
            'poster.mimes' => 'Event posters must be JPG, JPEG, PNG, or WEBP files only.',
            'poster.max' => 'Event posters must not be larger than 20MB.',
            'event_image.required_without' => 'Please choose an event poster image.',
            'event_image.image' => 'The poster must be a valid image file.',
            'event_image.mimes' => 'Event posters must be JPG, JPEG, PNG, or WEBP files only.',
            'event_image.max' => 'Event posters must not be larger than 20MB.',
        ]);

        $newPosterPath = $this->storePoster($request);

        $this->deletePoster($event);

        $event->forceFill(['event_image' => $newPosterPath])->save();
        $event->refresh();

        ActivityLog::record('event.poster_updated', 'Updated event poster: '.$event->title, $event);

        return response()->json([
            'message' => 'Poster image saved.',
            'poster' => $event->poster,
            'poster_url' => $event->poster_url,
        ]);
    }

    /**
     * Delete event
     */
    public function destroy(Event $event): RedirectResponse
    {
        if (! $event->allowsAdminChanges()) {
            return redirect()
                ->route('admin.events.show', $event)
                ->withErrors(['event' => 'This event can only be viewed because its status is '.ucfirst($event->status).'.']);
        }

        $eventTitle = $event->title;
        $eventOrganization = $event->organization;

        $event->delete();

        ActivityLog::record('event.deleted', 'Deleted event: '.$eventTitle, $event, auth()->user(), [
            'event_title' => $eventTitle,
            'organization' => $eventOrganization,
        ]);

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

        ActivityLog::record('registration.approved', 'Approved registration #'.$registration->id, $registration->fresh(['event', 'user']), auth()->user(), [
            'event_title' => $registration->event?->title,
            'student_name' => $registration->user?->name,
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

        ActivityLog::record('registration.rejected', 'Rejected registration #'.$registration->id, $registration->fresh(['event', 'user']), auth()->user(), [
            'event_title' => $registration->event?->title,
            'student_name' => $registration->user?->name,
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
            'organization' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,ongoing,completed,cancelled',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
            'event_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ], [
            'end_date.after_or_equal' => 'The event end date must be after the start date.',
            'organization.required' => 'Please enter the organization hosting this event.',
            'poster.mimes' => 'Event posters must be uploaded as JPG, JPEG, PNG, or WEBP files only.',
            'poster.max' => 'Event posters must not be larger than 20MB.',
            'event_image.mimes' => 'Event posters must be uploaded as JPG, JPEG, PNG, or WEBP files only.',
            'event_image.max' => 'Event posters must not be larger than 20MB.',
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

    private function organizationOptions()
    {
        return Event::query()
            ->whereNotNull('organization')
            ->where('organization', '!=', '')
            ->distinct()
            ->orderBy('organization')
            ->pluck('organization');
    }

    private function storePoster(Request $request): string
    {
        try {
            $path = $request->file('poster')
                ? $request->file('poster')->store('event-posters', 'public')
                : $request->file('event_image')->store('event-posters', 'public');
        } catch (Throwable) {
            $path = false;
        }

        if (! $path || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages([
                'poster' => 'The event poster could not be uploaded. Please try again.',
            ]);
        }

        return $path;
    }

    private function normalizePosterUpload(Request $request): void
    {
        if (! $request->hasFile('poster') && $request->hasFile('event_image')) {
            $request->files->set('poster', $request->file('event_image'));
        }
    }

    private function hasPosterUpload(Request $request): bool
    {
        return $request->file('poster')?->isValid()
            || $request->file('event_image')?->isValid();
    }

    private function deletePoster(Event $event): void
    {
        $poster = $event->getRawOriginal('event_image');

        if ($poster && ! filter_var($poster, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($poster)) {
            Storage::disk('public')->delete($poster);
        }
    }
}
