@extends('admin.layout')

@section('title', 'Reports Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Reports Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">
                Review event performance, registration activity, and attendance in one place.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button
                type="button"
                data-export-format="pdf"
                data-export-url="{{ route('admin.reports.export.pdf') }}"
                class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
            >
                Export PDF
            </button>
            <button
                type="button"
                data-export-format="excel"
                data-export-url="{{ route('admin.reports.export.excel') }}"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                Export Excel
            </button>
        </div>
    </div>

    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-900 p-6 text-white shadow-lg">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Generated</p>
                <p class="mt-2 text-2xl font-semibold">{{ $generatedAt->format('M d, Y') }}</p>
                <p class="text-sm text-blue-100">{{ $generatedAt->format('h:i A') }}</p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Top Event</p>
                <p class="mt-2 text-xl font-semibold">{{ $topEvent?->title ?? 'No event data' }}</p>
                <p class="text-sm text-blue-100">
                    {{ $topEvent ? $topEvent->registrations_count . ' registrations' : 'Adjust the filters to widen the report.' }}
                </p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Filter Status</p>
                <p class="mt-2 text-2xl font-semibold">{{ ! empty($filters['statuses']) ? collect($filters['statuses'])->map(fn ($status) => ucfirst($status))->implode(', ') : ($filters['status'] !== '' ? ucfirst($filters['status']) : 'All') }}</p>
                <p class="text-sm text-blue-100">
                    {{ $filters['date_from'] !== '' || $filters['date_to'] !== '' ? 'Date range applied' : 'No date range filter' }}
                </p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Most Used Location</p>
                <p class="mt-2 text-2xl font-semibold">{{ $topLocation['location'] ?? 'No location data' }}</p>
                <p class="text-sm text-blue-100">
                    {{ $topLocation ? $topLocation['event_count'] . ' events hosted here' : 'Add event locations to compare usage.' }}
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div>
                <label for="search" class="mb-2 block text-sm font-semibold text-gray-700">Search</label>
                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $filters['search'] }}"
                    placeholder="Event title or location"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                    <option value="">All statuses</option>
                    @foreach(['draft', 'published', 'ongoing', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="mb-2 block text-sm font-semibold text-gray-700">Start From</label>
                <input
                    id="date_from"
                    name="date_from"
                    type="date"
                    value="{{ $filters['date_from'] }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
            </div>
            <div>
                <label for="date_to" class="mb-2 block text-sm font-semibold text-gray-700">End To</label>
                <input
                    id="date_to"
                    name="date_to"
                    type="date"
                    value="{{ $filters['date_to'] }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Apply Filters
                </button>
                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Total Events</p>
            <p class="mt-3 text-4xl font-bold text-gray-900">{{ $summary['total_events'] }}</p>
            <p class="mt-2 text-sm text-gray-500">Events included in the current report.</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Registrations</p>
            <p class="mt-3 text-4xl font-bold text-gray-900">{{ $summary['total_registrations'] }}</p>
            <p class="mt-2 text-sm text-gray-500">{{ $summary['approved_registrations'] }} approved registrations.</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Attendance Rate</p>
            <p class="mt-3 text-4xl font-bold text-gray-900">{{ number_format($summary['average_attendance_rate'], 2) }}%</p>
            <p class="mt-2 text-sm text-gray-500">{{ $summary['attendance_records'] }} attendance records logged.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="rounded-2xl bg-white shadow">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-bold text-gray-800">Event Performance</h2>
                <p class="mt-1 text-sm text-gray-500">Registration and attendance totals for each matching event.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Schedule</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Registrations</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Approved</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Attendance</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 align-top">
                                    <p class="text-sm font-semibold text-gray-900">{{ $event->title }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $event->location }}</p>
                                    <p class="mt-1 text-xs text-gray-400">Created by {{ $event->creator?->name ?? 'Unknown' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <p>{{ $event->start_date->format('M d, Y h:i A') }}</p>
                                    <p class="mt-1 text-xs text-gray-400">to {{ $event->end_date->format('M d, Y h:i A') }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $statusClass = match ($event->status) {
                                            'draft' => 'bg-slate-100 text-slate-700',
                                            'published' => 'bg-blue-100 text-blue-700',
                                            'ongoing' => 'bg-emerald-100 text-emerald-700',
                                            'completed' => 'bg-violet-100 text-violet-700',
                                            'cancelled' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                        $attendanceRate = $event->approved_registrations_count > 0
                                            ? round(($event->attendance_records_count / $event->approved_registrations_count) * 100, 2)
                                            : 0;
                                    @endphp
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $event->registrations_count }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $event->approved_registrations_count }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $event->attendance_records_count }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-indigo-700">{{ number_format($attendanceRate, 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No events matched the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-bold text-gray-800">Status Breakdown</h2>
                <div class="mt-6 space-y-4">
                    @foreach($statusCounts as $status => $count)
                        @php
                            $percent = $summary['total_events'] > 0 ? round(($count / $summary['total_events']) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">{{ ucfirst($status) }}</span>
                                <span class="text-sm text-gray-500">{{ $count }} events</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-bold text-gray-800">Location Usage</h2>
                <p class="mt-1 text-sm text-gray-500">Most-used event locations in this report.</p>
                <div class="mt-6 space-y-4">
                    @php
                        $maxLocationEvents = $locationUsage->max('event_count') ?: 0;
                    @endphp
                    @forelse($locationUsage as $location)
                        @php
                            $percent = $maxLocationEvents > 0 ? round(($location['event_count'] / $maxLocationEvents) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">{{ $location['location'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $location['registrations_count'] }} registrations, {{ $location['attendance_records_count'] }} attendance records</p>
                                </div>
                                <span class="shrink-0 text-sm font-semibold text-gray-600">{{ $location['event_count'] }} events</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No matching event locations found.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-bold text-gray-800">Report Notes</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li>Total attendance uses records captured from event check-ins.</li>
                    <li>Attendance rate is based on approved registrations only.</li>
                    <li>Excel and PDF exports use the date range and statuses selected before export.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div id="exportToast" class="pointer-events-none fixed right-4 top-4 z-50 hidden w-[calc(100%-2rem)] max-w-sm rounded-2xl border px-5 py-4 text-sm font-semibold shadow-panel sm:right-6 sm:top-6"></div>

<div id="exportModal" class="fixed inset-0 z-40 hidden overflow-y-auto bg-slate-950/60 px-4 py-6 backdrop-blur-sm" aria-hidden="true">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600">Reports Dashboard</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Export Reports</h2>
                    </div>
                    <button type="button" id="closeExportModal" class="rounded-full border border-slate-200 px-3 py-1 text-lg font-semibold leading-none text-slate-500 transition hover:bg-slate-50" aria-label="Close export modal">
                        &times;
                    </button>
                </div>
            </div>

            <form id="exportForm" class="space-y-6 px-6 py-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Export format</p>
                            <p id="exportFormatLabel" class="mt-1 text-sm text-slate-500">PDF</p>
                        </div>
                        <div id="exportPreviewCount" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-brand-700 shadow-sm">
                            0 reports selected
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-600">Date Range</h3>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-export-preset="month" class="rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:border-brand-200 hover:bg-brand-100">This Month</button>
                            <button type="button" data-export-preset="three-months" class="rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:border-brand-200 hover:bg-brand-100">Last 3 Months</button>
                            <button type="button" data-export-preset="year" class="rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:border-brand-200 hover:bg-brand-100">This Year</button>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="exportDateFrom" class="mb-2 block text-sm font-semibold text-slate-700">From</label>
                            <input id="exportDateFrom" name="date_from" type="date" value="{{ $filters['date_from'] ?: '2026-04-01' }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </div>
                        <div>
                            <label for="exportDateTo" class="mb-2 block text-sm font-semibold text-slate-700">To</label>
                            <input id="exportDateTo" name="date_to" type="date" value="{{ $filters['date_to'] ?: '2026-10-31' }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </div>
                    </div>
                    <p id="exportDateError" class="mt-2 hidden text-sm font-semibold text-rose-600">From date cannot be later than To date.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-600">Event Status</h3>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-brand-700">
                            <input id="selectAllStatuses" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" checked>
                            Select All Statuses
                        </label>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach(['ongoing' => 'Ongoing', 'cancelled' => 'Cancelled', 'completed' => 'Done/Completed'] as $status => $label)
                            <label class="export-status-option flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-brand-200 hover:bg-brand-50">
                                <input type="checkbox" name="statuses[]" value="{{ $status }}" class="export-status-checkbox h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" checked>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p id="exportStatusError" class="mt-2 hidden text-sm font-semibold text-rose-600">Select at least one status before exporting.</p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" id="cancelExportModal" class="inline-flex justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" id="confirmExportButton" class="inline-flex justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70">
                        Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('exportModal');
        const form = document.getElementById('exportForm');
        const dateFrom = document.getElementById('exportDateFrom');
        const dateTo = document.getElementById('exportDateTo');
        const dateError = document.getElementById('exportDateError');
        const statusError = document.getElementById('exportStatusError');
        const selectAll = document.getElementById('selectAllStatuses');
        const statusInputs = Array.from(document.querySelectorAll('.export-status-checkbox'));
        const formatLabel = document.getElementById('exportFormatLabel');
        const previewCount = document.getElementById('exportPreviewCount');
        const submitButton = document.getElementById('confirmExportButton');
        const toast = document.getElementById('exportToast');
        const previewEvents = @json($exportPreviewEvents);
        let exportUrl = '';
        let exportFormat = 'pdf';

        const toDateInput = (date) => date.toISOString().slice(0, 10);
        const selectedStatuses = () => statusInputs.filter((input) => input.checked).map((input) => input.value);

        const showToast = (message, type = 'success') => {
            toast.textContent = message;
            toast.className = 'pointer-events-none fixed right-4 top-4 z-50 w-[calc(100%-2rem)] max-w-sm rounded-2xl border px-5 py-4 text-sm font-semibold shadow-panel sm:right-6 sm:top-6 ' + (
                type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-rose-200 bg-rose-50 text-rose-700'
            );
            window.setTimeout(() => toast.classList.add('hidden'), 4200);
        };

        const setModalOpen = (isOpen) => {
            modal.classList.toggle('hidden', !isOpen);
            modal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            document.body.classList.toggle('overflow-hidden', isOpen);
        };

        const updateSelectAllState = () => {
            const checkedCount = selectedStatuses().length;
            selectAll.checked = checkedCount === statusInputs.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < statusInputs.length;
        };

        const updatePreview = () => {
            const statuses = selectedStatuses();
            const from = dateFrom.value;
            const to = dateTo.value;
            const total = previewEvents.filter((event) => {
                const matchesStatus = statuses.includes(event.status);
                const matchesFrom = !from || event.start >= from;
                const matchesTo = !to || event.end <= to;

                return matchesStatus && matchesFrom && matchesTo;
            }).length;

            previewCount.textContent = `${total} ${total === 1 ? 'report' : 'reports'} selected`;
        };

        const validate = () => {
            const hasDateError = dateFrom.value && dateTo.value && dateFrom.value > dateTo.value;
            const hasStatusError = selectedStatuses().length === 0;

            dateError.classList.toggle('hidden', !hasDateError);
            statusError.classList.toggle('hidden', !hasStatusError);

            return !hasDateError && !hasStatusError;
        };

        document.querySelectorAll('[data-export-format]').forEach((button) => {
            button.addEventListener('click', () => {
                exportFormat = button.dataset.exportFormat;
                exportUrl = button.dataset.exportUrl;
                formatLabel.textContent = exportFormat === 'pdf' ? 'PDF' : 'Excel';
                submitButton.textContent = `Export ${formatLabel.textContent}`;
                updateSelectAllState();
                updatePreview();
                setModalOpen(true);
            });
        });

        document.getElementById('closeExportModal').addEventListener('click', () => setModalOpen(false));
        document.getElementById('cancelExportModal').addEventListener('click', () => setModalOpen(false));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                setModalOpen(false);
            }
        });

        selectAll.addEventListener('change', () => {
            statusInputs.forEach((input) => {
                input.checked = selectAll.checked;
            });
            updateSelectAllState();
            validate();
            updatePreview();
        });

        statusInputs.forEach((input) => input.addEventListener('change', () => {
            updateSelectAllState();
            validate();
            updatePreview();
        }));

        [dateFrom, dateTo].forEach((input) => input.addEventListener('input', () => {
            validate();
            updatePreview();
        }));

        document.querySelectorAll('[data-export-preset]').forEach((button) => {
            button.addEventListener('click', () => {
                const now = new Date();
                const preset = button.dataset.exportPreset;
                let from;
                let to;

                if (preset === 'month') {
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                    to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                } else if (preset === 'three-months') {
                    from = new Date(now.getFullYear(), now.getMonth() - 2, 1);
                    to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                } else {
                    from = new Date(now.getFullYear(), 0, 1);
                    to = new Date(now.getFullYear(), 11, 31);
                }

                dateFrom.value = toDateInput(from);
                dateTo.value = toDateInput(to);
                validate();
                updatePreview();
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!validate()) {
                return;
            }

            const params = new URLSearchParams();
            params.set('date_from', dateFrom.value);
            params.set('date_to', dateTo.value);
            selectedStatuses().forEach((status) => params.append('statuses[]', status));

            submitButton.disabled = true;
            submitButton.textContent = `Generating ${formatLabel.textContent}...`;

            try {
                const response = await fetch(`${exportUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': exportFormat === 'pdf' ? 'application/pdf' : 'application/vnd.ms-excel',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('The export could not be generated.');
                }

                const blob = await response.blob();
                const disposition = response.headers.get('Content-Disposition') || '';
                const filenameMatch = disposition.match(/filename="?([^"]+)"?/);
                const filename = filenameMatch ? filenameMatch[1] : `admin-report-${Date.now()}.${exportFormat === 'pdf' ? 'pdf' : 'xls'}`;
                const downloadUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = downloadUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(downloadUrl);
                setModalOpen(false);
                showToast(`${formatLabel.textContent} export generated successfully.`);
            } catch (error) {
                showToast(error.message || 'Export failed. Please try again.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = `Export ${formatLabel.textContent}`;
            }
        });

        updatePreview();
    })();
</script>
@endpush
