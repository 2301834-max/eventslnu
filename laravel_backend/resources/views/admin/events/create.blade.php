@extends('admin.layout')

@section('title', 'Register Event')

@section('content')
<div class="space-y-8">
    <section class="rounded-[2rem] bg-gradient-to-r from-brand-700 via-brand-600 to-sky-500 px-8 py-8 text-white shadow-panel">
        <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-blue-100">New Event</p>
                <h2 class="mt-3 text-4xl font-semibold tracking-tight">Create an event with clean details and a valid future schedule.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-blue-50">
                    Past start times are now blocked to avoid invalid registrations and inaccurate reporting.
                </p>
            </div>
            <div class="rounded-3xl border border-white/20 bg-white/10 p-6 backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-100">Reminder</p>
                <ul class="mt-4 space-y-3 text-sm text-blue-50">
                    <li>Set the start date to the current time or later.</li>
                    <li>End date must come after the start date.</li>
                    <li>Use Published when the event is ready for student registration.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-panel">
        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-6">
                    <div>
                        <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Event Title</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="Leadership Summit 2026"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('title') border-rose-400 @enderror"
                            required
                        >
                        @error('title')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="organization" class="mb-2 block text-sm font-semibold text-slate-700">Organization</label>
                        <input
                            type="text"
                            id="organization"
                            name="organization"
                            value="{{ old('organization') }}"
                            list="organization-options"
                            placeholder="Student Council"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('organization') border-rose-400 @enderror"
                            required
                        >
                        <datalist id="organization-options">
                            @foreach($organizations as $organization)
                                <option value="{{ $organization }}"></option>
                            @endforeach
                        </datalist>
                        @error('organization')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="7"
                            placeholder="Add the event purpose, agenda highlights, and who should attend."
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('description') border-rose-400 @enderror"
                            required
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="mb-2 block text-sm font-semibold text-slate-700">Location</label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="{{ old('location') }}"
                            placeholder="Main Auditorium"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('location') border-rose-400 @enderror"
                            required
                        >
                        @error('location')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-6 rounded-3xl bg-slate-50 p-6">
                    <div>
                        <label for="start_date" class="mb-2 block text-sm font-semibold text-slate-700">Start Date and Time</label>
                        <input
                            type="datetime-local"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date') }}"
                            min="{{ $minimumStartDate }}"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('start_date') border-rose-400 @enderror"
                            required
                        >
                        <p class="mt-2 text-xs text-slate-500">Past date and time selections are not allowed.</p>
                        @error('start_date')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="mb-2 block text-sm font-semibold text-slate-700">End Date and Time</label>
                        <input
                            type="datetime-local"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            min="{{ old('start_date', $minimumStartDate) }}"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('end_date') border-rose-400 @enderror"
                            required
                        >
                        @error('end_date')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_participants" class="mb-2 block text-sm font-semibold text-slate-700">Max Participants</label>
                        <input
                            type="number"
                            id="max_participants"
                            name="max_participants"
                            value="{{ old('max_participants') }}"
                            min="1"
                            placeholder="100"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('max_participants') border-rose-400 @enderror"
                            required
                        >
                        @error('max_participants')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('status') border-rose-400 @enderror"
                            required
                        >
                            <option value="">Select status</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="ongoing" {{ old('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="poster" class="mb-2 block text-sm font-semibold text-slate-700">Event Poster</label>
                        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3">
                            <div class="flex h-44 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-brand-700 via-brand-600 to-sky-500">
                                <img
                                    id="eventImagePreview"
                                    src=""
                                    alt="Event poster preview"
                                    class="hidden h-full w-full object-cover"
                                >
                                <span id="eventImageFallback" class="text-sm font-bold uppercase tracking-[0.25em] text-white">Event Poster</span>
                            </div>
                        </div>
                        <input
                            type="file"
                            id="poster"
                            name="poster"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('poster') border-rose-400 @enderror"
                        >
                        <p class="mt-2 text-xs text-slate-500">JPG, JPEG, PNG, or WEBP up to 20MB.</p>
                        @error('poster')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 border-t border-slate-200 pt-6">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Create Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const eventImageInput = document.getElementById('poster');
    const eventImagePreview = document.getElementById('eventImagePreview');

    if (startDateInput && endDateInput) {
        const syncEndDateMin = () => {
            endDateInput.min = startDateInput.value || '{{ $minimumStartDate }}';
        };

        startDateInput.addEventListener('change', syncEndDateMin);
        syncEndDateMin();
    }

    if (eventImageInput && eventImagePreview) {
        eventImageInput.addEventListener('change', () => {
            const file = eventImageInput.files?.[0];

            if (!file) {
                eventImagePreview.src = '';
                eventImagePreview.classList.add('hidden');
                document.getElementById('eventImageFallback')?.classList.remove('hidden');
                return;
            }

            document.getElementById('eventImageFallback')?.classList.add('hidden');
            eventImagePreview.classList.remove('hidden');
            eventImagePreview.src = URL.createObjectURL(file);
            eventImagePreview.onload = () => URL.revokeObjectURL(eventImagePreview.src);
        });
    }
</script>
@endsection
