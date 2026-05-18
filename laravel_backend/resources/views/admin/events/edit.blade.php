@extends('admin.layout')

@section('title', 'Edit Event')

@section('content')
<div class="space-y-8">
    <section class="rounded-[2rem] bg-gradient-to-r from-slate-950 via-slate-900 to-slate-700 px-8 py-8 text-white shadow-panel">
        <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200">Event Maintenance</p>
                <h2 class="mt-3 text-4xl font-semibold tracking-tight">Update event details without letting schedules drift into invalid past dates.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200">
                    You can still revise completed event content, but changing the schedule to a past time is blocked.
                </p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-200">Current Event</p>
                <p class="mt-4 text-2xl font-semibold">{{ $event->title }}</p>
                <p class="mt-2 text-sm text-slate-300">{{ $event->organization }}</p>
                <p class="mt-2 text-sm text-slate-300">{{ $event->location }}</p>
                <p class="mt-4 text-xs uppercase tracking-[0.3em] text-slate-400">{{ ucfirst($event->status) }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-panel">
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-6">
                    <div>
                        <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Event Title</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $event->title) }}"
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
                            value="{{ old('organization', $event->organization) }}"
                            list="organization-options"
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
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('description') border-rose-400 @enderror"
                            required
                        >{{ old('description', $event->description) }}</textarea>
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
                            value="{{ old('location', $event->location) }}"
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
                            value="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}"
                            min="{{ $minimumStartDate }}"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('start_date') border-rose-400 @enderror"
                            required
                        >
                        <p class="mt-2 text-xs text-slate-500">Changing to an already-passed time is not allowed.</p>
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
                            value="{{ old('end_date', $event->end_date->format('Y-m-d\TH:i')) }}"
                            min="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}"
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
                            value="{{ old('max_participants', $event->max_participants) }}"
                            min="1"
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
                            <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="ongoing" {{ old('status', $event->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="poster" class="mb-2 block text-sm font-semibold text-slate-700">Event Poster</label>
                        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3">
                            <div class="flex h-44 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-brand-700 via-brand-600 to-sky-500">
                                @if($event->poster)
                                    <img
                                        id="eventImagePreview"
                                        src="{{ $event->poster_url }}"
                                        alt="{{ $event->title }} poster"
                                        class="h-full w-full object-cover"
                                        onerror="this.classList.add('hidden'); document.getElementById('eventImageFallback')?.classList.remove('hidden');"
                                    >
                                    <span id="eventImageFallback" class="hidden text-sm font-bold uppercase tracking-[0.25em] text-white">POSTER</span>
                                @else
                                    <img
                                        id="eventImagePreview"
                                        src=""
                                        alt="{{ $event->title }} poster"
                                        class="hidden h-full w-full object-cover"
                                    >
                                    <span id="eventImageFallback" class="text-sm font-bold uppercase tracking-[0.25em] text-white">POSTER</span>
                                @endif
                            </div>
                        </div>
                        <input
                            type="file"
                            id="poster"
                            name="poster"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            data-upload-url="{{ route('admin.events.poster.update', $event) }}"
                            class="w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('poster') border-rose-400 @enderror"
                        >
                        <p class="mt-2 text-xs text-slate-500">JPG, JPEG, PNG, or WEBP up to 20MB.</p>
                        <p id="eventImageStatus" class="mt-2 hidden text-sm font-semibold"></p>
                        @error('poster')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 border-t border-slate-200 pt-6">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Update Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</div>

<script>
    const editStartDateInput = document.getElementById('start_date');
    const editEndDateInput = document.getElementById('end_date');
    const editEventImageInput = document.getElementById('poster');
    const editEventImagePreview = document.getElementById('eventImagePreview');
    const editEventImageFallback = document.getElementById('eventImageFallback');
    const editEventImageStatus = document.getElementById('eventImageStatus');
    const csrfToken = '{{ csrf_token() }}';

    if (editStartDateInput && editEndDateInput) {
        const syncEditEndDateMin = () => {
            editEndDateInput.min = editStartDateInput.value || '{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}';
        };

        editStartDateInput.addEventListener('change', syncEditEndDateMin);
        syncEditEndDateMin();
    }

    if (editEventImageInput && editEventImagePreview) {
        const currentPoster = editEventImagePreview.src;
        const setPosterStatus = (message, isError = false) => {
            if (!editEventImageStatus) return;

            editEventImageStatus.textContent = message;
            editEventImageStatus.classList.remove('hidden', 'text-emerald-700', 'text-rose-600');
            editEventImageStatus.classList.add(isError ? 'text-rose-600' : 'text-emerald-700');
        };

        editEventImageInput.addEventListener('change', async () => {
            const file = editEventImageInput.files?.[0];

            if (!file) {
                editEventImagePreview.src = currentPoster;
                editEventImageFallback?.classList.toggle('hidden', Boolean(currentPoster));
                return;
            }

            editEventImageFallback?.classList.add('hidden');
            editEventImagePreview.classList.remove('hidden');
            editEventImagePreview.src = URL.createObjectURL(file);
            editEventImagePreview.onload = () => URL.revokeObjectURL(editEventImagePreview.src);

            const uploadUrl = editEventImageInput.dataset.uploadUrl;
            const formData = new FormData();
            formData.append('poster', file);
            setPosterStatus('Saving poster...');

            try {
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const errors = payload.errors ? Object.values(payload.errors).flat() : [];
                    throw new Error(errors[0] || payload.message || 'Poster upload failed.');
                }

                if (payload.poster_url) {
                    editEventImagePreview.src = `${payload.poster_url}?v=${Date.now()}`;
                    editEventImagePreview.classList.remove('hidden');
                    editEventImageFallback?.classList.add('hidden');
                }

                setPosterStatus('Poster saved. This image will appear on admin and student event pages.');
            } catch (error) {
                setPosterStatus(error.message || 'Poster upload failed.', true);
            }
        });
    }
</script>
@endsection
