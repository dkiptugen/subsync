<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\User;
use App\Traits\Meta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $this->data['filters'] = $filters;
        $this->data['chartData'] = $this->chartData($filters);

        return view('modules.reportds.subscribers', $this->data);
    }

    public function reg_datatable(ReportFilterRequest $request): JsonResponse
    {
        $filters = $this->filters($request);
        $query = $this->subscriberQuery($filters);
        $columns = [
            1 => 'name',
            2 => 'email',
            4 => 'status',
            7 => 'last_login',
            8 => 'created_at',
        ];

        return response()->json($this->datatableResponse(
            request: $request,
            query: $query,
            searchable: function (Builder $searchQuery, string $search): void {
                $searchQuery->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('surname', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhereHas('organization', function (Builder $organizationQuery) use ($search): void {
                            $organizationQuery->where('name', 'LIKE', "%{$search}%");
                        });
                });
            },
            orderColumns: $columns,
            defaultOrder: 'created_at',
            rowMapper: function (User $subscriber, int $position): array {
                return [
                    'pos' => $position,
                    'name' => trim($subscriber->name.' '.$subscriber->surname),
                    'email' => $subscriber->email,
                    'organization' => $subscriber->organization?->name ?? '-',
                    'status' => $subscriber->status ? 'Active' : 'Inactive',
                    'phone' => $subscriber->phone ?? '-',
                    'login_type' => $subscriber->providers->pluck('provider')->implode(', ') ?: 'Direct',
                    'last_login' => $subscriber->last_login ? Carbon::parse($subscriber->last_login)->format('M d, Y H:i') : '-',
                    'created_at' => $subscriber->created_at->format('M d, Y H:i'),
                ];
            }
        ));
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
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     */
    private function subscriberQuery(array $filters): Builder
    {
        return User::query()
            ->with(['organization', 'providers'])
            ->where(function (Builder $query): void {
                $query->where('type', 'customer')
                    ->orWhere('type', 'organization');
            })
            ->whereBetween('created_at', [$filters['startdate'], $filters['enddate']]);
    }

    /**
     * @param  callable(Builder, string): void  $searchable
     * @param  array<int, string>  $orderColumns
     * @param  callable(mixed, int): array<string, mixed>  $rowMapper
     * @return array{draw: int, recordsTotal: int, recordsFiltered: int, data: array<int, array<string, mixed>>}
     */
    private function datatableResponse(
        Request $request,
        Builder $query,
        callable $searchable,
        array $orderColumns,
        string $defaultOrder,
        callable $rowMapper
    ): array {
        $totalData = (clone $query)->count();
        $search = (string) $request->input('search.value', '');

        if ($search !== '') {
            $searchable($query, $search);
        }

        $totalFiltered = (clone $query)->count();
        $limit = (int) $request->input('length', 25);
        $start = (int) $request->input('start', 0);
        $orderColumn = (int) $request->input('order.0.column', 0);
        $order = $orderColumns[$orderColumn] ?? $defaultOrder;
        $direction = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';

        $items = $query
            ->orderBy($order, $direction)
            ->when($limit > 0, function (Builder $pageQuery) use ($start, $limit): void {
                $pageQuery->offset($start)->limit($limit);
            })
            ->get();

        $position = $start + 1;

        return [
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $items->map(function ($item) use (&$position, $rowMapper): array {
                return $rowMapper($item, $position++);
            })->all(),
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
