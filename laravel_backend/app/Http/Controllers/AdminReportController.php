<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminReportController extends Controller
{
    private const PDF_PAGE_WIDTH = 612;
    private const PDF_PAGE_HEIGHT = 792;

    public function index(Request $request): View
    {
        $report = $this->buildReport($request);

        return view('admin.reports.index', $report);
    }

    public function exportExcel(Request $request): Response
    {
        $report = $this->buildReport($request);
        $filename = 'admin-report-' . now()->format('Ymd-His') . '.xls';

        return response()
            ->view('admin.reports.excel', $report)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportPdf(Request $request): Response
    {
        $report = $this->buildReport($request);
        $pdf = $this->buildStyledPdf($report);
        $filename = 'admin-report-' . now()->format('Ymd-His') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildReport(Request $request): array
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:draft,published,ongoing,completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $events = $this->filteredEventsQuery($filters)
            ->withCount([
                'registrations',
                'registrations as approved_registrations_count' => fn ($query) => $query->where('status', 'approved'),
                'attendanceRecords',
            ])
            ->with('creator')
            ->orderByDesc('start_date')
            ->get();

        $totalApproved = (int) $events->sum('approved_registrations_count');
        $totalAttendance = (int) $events->sum('attendance_records_count');

        $summary = [
            'total_events' => $events->count(),
            'total_registrations' => (int) $events->sum('registrations_count'),
            'approved_registrations' => $totalApproved,
            'attendance_records' => $totalAttendance,
            'average_attendance_rate' => $totalApproved > 0
                ? round(($totalAttendance / $totalApproved) * 100, 2)
                : 0,
            'completed_events' => $events->where('status', 'completed')->count(),
        ];

        $statusCounts = collect(['draft', 'published', 'ongoing', 'completed', 'cancelled'])
            ->mapWithKeys(fn (string $status) => [$status => $events->where('status', $status)->count()]);

        $topEvent = $events->sortByDesc('registrations_count')->first();

        return [
            'events' => $events,
            'filters' => [
                'status' => $filters['status'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'search' => $filters['search'] ?? '',
            ],
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'topEvent' => $topEvent,
            'generatedAt' => now(),
        ];
    }

    private function filteredEventsQuery(array $filters)
    {
        return Event::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('start_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('end_date', '<=', $dateTo))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%');
                });
            });
    }

    private function buildStyledPdf(array $report): string
    {
        $pages = [];
        $pageNumber = 1;
        $page = $this->newPdfPage();

        $this->drawPdfHeader($page, $report, $pageNumber);
        $this->drawSummarySection($page, $report);
        $this->drawFiltersSection($page, $report);
        $this->drawStatusSection($page, $report);
        $this->drawTableHeader($page, 326);

        $currentTop = 352;

        foreach ($report['events'] as $index => $event) {
            if ($currentTop > 706) {
                $pages[] = $page;
                $pageNumber++;
                $page = $this->newPdfPage();
                $this->drawPdfHeader($page, $report, $pageNumber, true);
                $this->drawTableHeader($page, 114);
                $currentTop = 140;
            }

            $this->drawEventRow($page, $currentTop, $event, $index);
            $currentTop += 24;
        }

        if ($report['events']->isEmpty()) {
            $this->addPdfText($page, 'No events matched the selected filters.', 46, $currentTop + 8, 10, [107, 114, 128]);
        }

        $pages[] = $page;

        return $this->compilePdf($pages);
    }

    private function newPdfPage(): array
    {
        return ['commands' => []];
    }

    private function drawPdfHeader(array &$page, array $report, int $pageNumber, bool $continued = false): void
    {
        $this->addPdfFilledRect($page, 0, 0, self::PDF_PAGE_WIDTH, 84, [15, 23, 42]);
        $this->addPdfText($page, 'LNU Smart Events System', 40, 38, 22, [255, 255, 255], true);
        $this->addPdfText($page, $continued ? 'Admin Report Export - Continued' : 'Admin Report Export', 40, 60, 11, [191, 219, 254]);
        $this->addPdfText($page, 'Generated ' . $report['generatedAt']->format('M d, Y h:i A'), 408, 38, 10, [226, 232, 240], true);
        $this->addPdfText($page, 'Page ' . $pageNumber, 520, 60, 10, [191, 219, 254], true);
    }

    private function drawSummarySection(array &$page, array $report): void
    {
        $cards = [
            ['label' => 'Total Events', 'value' => (string) $report['summary']['total_events'], 'color' => [224, 231, 255]],
            ['label' => 'Registrations', 'value' => (string) $report['summary']['total_registrations'], 'color' => [220, 252, 231]],
            ['label' => 'Attendance', 'value' => (string) $report['summary']['attendance_records'], 'color' => [254, 249, 195]],
            ['label' => 'Avg Rate', 'value' => number_format($report['summary']['average_attendance_rate'], 2) . '%', 'color' => [254, 226, 226]],
        ];

        $this->addPdfText($page, 'Performance Snapshot', 40, 112, 12, [30, 41, 59], true);

        foreach ($cards as $index => $card) {
            $x = 40 + (($index % 2) * 266);
            $y = 124 + (intdiv($index, 2) * 74);

            $this->addPdfFilledRect($page, $x, $y, 246, 58, $card['color']);
            $this->addPdfStrokeRect($page, $x, $y, 246, 58, [203, 213, 225]);
            $this->addPdfText($page, $card['label'], $x + 14, $y + 22, 10, [71, 85, 105], true);
            $this->addPdfText($page, $card['value'], $x + 14, $y + 44, 18, [15, 23, 42], true);
        }
    }

    private function drawFiltersSection(array &$page, array $report): void
    {
        $this->addPdfFilledRect($page, 40, 282, 532, 32, [248, 250, 252]);
        $this->addPdfStrokeRect($page, 40, 282, 532, 32, [226, 232, 240]);

        $filtersLine = sprintf(
            'Filters  |  Status: %s  |  Date: %s to %s  |  Search: %s',
            $report['filters']['status'] !== '' ? ucfirst($report['filters']['status']) : 'All',
            $report['filters']['date_from'] !== '' ? $report['filters']['date_from'] : 'Any',
            $report['filters']['date_to'] !== '' ? $report['filters']['date_to'] : 'Any',
            $report['filters']['search'] !== '' ? $this->truncate($report['filters']['search'], 26) : 'None'
        );

        $this->addPdfText($page, $filtersLine, 52, 302, 9, [51, 65, 85]);
    }

    private function drawStatusSection(array &$page, array $report): void
    {
        $this->addPdfText($page, 'Status Breakdown', 40, 326, 12, [30, 41, 59], true);

        $x = 40;

        foreach ($report['statusCounts'] as $status => $count) {
            $label = ucfirst($status) . ': ' . $count;
            $width = 94;

            $this->addPdfFilledRect($page, $x, 338, $width, 20, [238, 242, 255]);
            $this->addPdfStrokeRect($page, $x, 338, $width, 20, [199, 210, 254]);
            $this->addPdfText($page, $label, $x + 8, 351, 8, [55, 65, 81], true);
            $x += $width + 10;
        }
    }

    private function drawTableHeader(array &$page, int $top): void
    {
        $this->addPdfText($page, 'Event Performance', 40, $top - 12, 12, [30, 41, 59], true);
        $this->addPdfFilledRect($page, 40, $top, 532, 24, [30, 64, 175]);

        $columns = [
            ['x' => 48, 'label' => 'Event'],
            ['x' => 220, 'label' => 'Schedule'],
            ['x' => 318, 'label' => 'Status'],
            ['x' => 390, 'label' => 'Regs'],
            ['x' => 438, 'label' => 'Approved'],
            ['x' => 502, 'label' => 'Attend'],
            ['x' => 546, 'label' => 'Rate'],
        ];

        foreach ($columns as $column) {
            $this->addPdfText($page, $column['label'], $column['x'], $top + 16, 9, [255, 255, 255], true);
        }
    }

    private function drawEventRow(array &$page, int $top, object $event, int $index): void
    {
        $fill = $index % 2 === 0 ? [255, 255, 255] : [248, 250, 252];
        $attendanceRate = $event->approved_registrations_count > 0
            ? number_format(($event->attendance_records_count / $event->approved_registrations_count) * 100, 1) . '%'
            : '0.0%';

        $this->addPdfFilledRect($page, 40, $top, 532, 22, $fill);
        $this->addPdfStrokeRect($page, 40, $top, 532, 22, [226, 232, 240]);

        $this->addPdfText($page, $this->truncate($event->title, 28), 48, $top + 15, 8, [31, 41, 55], true);
        $this->addPdfText($page, $event->start_date->format('M d, Y'), 220, $top + 15, 8, [71, 85, 105]);
        $this->addPdfText($page, ucfirst($event->status), 318, $top + 15, 8, [71, 85, 105], true);
        $this->addPdfText($page, (string) $event->registrations_count, 394, $top + 15, 8, [31, 41, 55], true);
        $this->addPdfText($page, (string) $event->approved_registrations_count, 446, $top + 15, 8, [31, 41, 55], true);
        $this->addPdfText($page, (string) $event->attendance_records_count, 510, $top + 15, 8, [31, 41, 55], true);
        $this->addPdfText($page, $attendanceRate, 548, $top + 15, 8, [30, 64, 175], true);
    }

    private function compilePdf(array $pages): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];

        $pageObjectIds = [];
        $nextObjectId = 5;

        foreach ($pages as $page) {
            $pageObjectId = $nextObjectId++;
            $contentObjectId = $nextObjectId++;
            $pageObjectIds[] = $pageObjectId;

            $content = implode("\n", $page['commands']);

            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PDF_PAGE_WIDTH . ' ' . self::PDF_PAGE_HEIGHT . "] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentObjectId} 0 R >>";
            $objects[$contentObjectId] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";
        }

        $kids = implode(' ', array_map(fn (int $id) => "{$id} 0 R", $pageObjectIds));
        $objects[2] = '<< /Type /Pages /Kids [ ' . $kids . ' ] /Count ' . count($pageObjectIds) . ' >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $objectId => $body) {
            $offsets[$objectId] = strlen($pdf);
            $pdf .= $objectId . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_keys($objects) as $objectId) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$objectId]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function addPdfFilledRect(array &$page, float $x, float $top, float $width, float $height, array $rgb): void
    {
        $bottom = self::PDF_PAGE_HEIGHT - $top - $height;

        $page['commands'][] = sprintf(
            "q %.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f Q",
            $rgb[0] / 255,
            $rgb[1] / 255,
            $rgb[2] / 255,
            $x,
            $bottom,
            $width,
            $height
        );
    }

    private function addPdfStrokeRect(array &$page, float $x, float $top, float $width, float $height, array $rgb): void
    {
        $bottom = self::PDF_PAGE_HEIGHT - $top - $height;

        $page['commands'][] = sprintf(
            "q 0.7 w %.3F %.3F %.3F RG %.2F %.2F %.2F %.2F re S Q",
            $rgb[0] / 255,
            $rgb[1] / 255,
            $rgb[2] / 255,
            $x,
            $bottom,
            $width,
            $height
        );
    }

    private function addPdfText(array &$page, string $text, float $x, float $top, int $size = 10, array $rgb = [31, 41, 55], bool $bold = false): void
    {
        $escaped = $this->escapePdfText($text);
        $font = $bold ? 'F2' : 'F1';
        $y = self::PDF_PAGE_HEIGHT - $top;

        $page['commands'][] = sprintf(
            "BT /%s %d Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
            $font,
            $size,
            $rgb[0] / 255,
            $rgb[1] / 255,
            $rgb[2] / 255,
            $x,
            $y,
            $escaped
        );
    }

    private function escapePdfText(string $value): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            preg_replace('/[^\x20-\x7E]/', '?', $value) ?? $value
        );
    }

    private function truncate(string $value, int $length): string
    {
        if (strlen($value) <= $length) {
            return $value;
        }

        return substr($value, 0, max(0, $length - 3)) . '...';
    }
}
