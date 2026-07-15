<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\User;
use App\Traits\Meta;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportsController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function reg_index(ReportFilterRequest $request): View
    {
        $filters = $this->filters($request);

        $this->data['subscribers'] = User::query()
            ->with(['organization', 'providers'])
            ->where(function ($query): void {
                $query->where('type', 'customer')
                    ->orWhere('type', 'organization');
            })
            ->whereBetween('created_at', [$filters['startdate'], $filters['enddate']])
            ->latest()
            ->paginate(25)
            ->withQueryString();
        $this->data['filters'] = $filters;
        $this->data['chartData'] = $this->chartData($filters);

        return view('modules.reportds.subscribers', $this->data);
    }

    /**
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     * @return array<string, array<string, array<int, int|string>>>
     */
    private function chartData(array $filters): array
    {
        $dailyRegistrations = User::query()
            ->where(function ($query): void {
                $query->where('type', 'customer')
                    ->orWhere('type', 'organization');
            })
            ->whereBetween('created_at', [$filters['startdate'], $filters['enddate']])
            ->selectRaw('DATE(created_at) as report_date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->pluck('total', 'report_date');

        $statusTotals = User::query()
            ->where(function ($query): void {
                $query->where('type', 'customer')
                    ->orWhere('type', 'organization');
            })
            ->whereBetween('created_at', [$filters['startdate'], $filters['enddate']])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'dailyRegistrations' => [
                'labels' => $dailyRegistrations->keys()->map(fn ($date): string => Carbon::parse($date)->format('M d'))->values()->all(),
                'data' => $dailyRegistrations->values()->map(fn ($total): int => (int) $total)->all(),
            ],
            'registrationStatus' => [
                'labels' => ['Active', 'Inactive'],
                'data' => [
                    (int) ($statusTotals[1] ?? 0),
                    (int) $statusTotals->except([1])->sum(),
                ],
            ],
        ];
    }

    /**
     * @return array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}
     */
    private function filters(ReportFilterRequest $request): array
    {
        $validated = $request->validated();

        return [
            'startdate' => Carbon::parse($validated['startdate'] ?? now()->startOfMonth()->toDateString())->startOfDay(),
            'enddate' => Carbon::parse($validated['enddate'] ?? now()->toDateString())->endOfDay(),
            'product' => [],
            'ratetype' => [],
            'status' => null,
        ];
    }
}
