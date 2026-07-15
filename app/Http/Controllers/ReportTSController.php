<?php

namespace App\Http\Controllers;

use App\Exports\SubscriberExport;
use App\Exports\SubscriptionReportExport;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Product;
use App\Models\RateType;
use App\Models\Subscription;
use App\Traits\Meta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportTSController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function subscribers_form()
    {
        $this->data['products'] = Product::get();

        return view('modules.reports.subscribers', $this->data);
    }

    public function subscribers_export(Request $request)
    {

        $d = Excel::download(new SubscriberExport($request), 'subscribers-'.$request->startdate.'-'.$request->enddate.'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        if ($d) {
            return $d;
        }

    }

    public function subscriptions_form(ReportFilterRequest $request): View
    {
        $filters = $this->filters($request);

        $this->data['products'] = Product::orderBy('product_name')->get();
        $this->data['rateTypes'] = RateType::orderBy('name')->get();
        $this->data['subscriptions'] = $this->subscriptionQuery($filters)
            ->latest('subscription_date')
            ->paginate(25)
            ->withQueryString();
        $this->data['filters'] = $filters;
        $this->data['chartData'] = $this->chartData($filters);

        return view('modules.reportds.subscriptions', $this->data);
    }

    public function subscriptions(ReportFilterRequest $request)
    {
        $filters = $this->filters($request);

        return Excel::download(
            new SubscriptionReportExport($filters),
            'subscriptions-'.$filters['startdate']->toDateString().'-'.$filters['enddate']->toDateString().'.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /**
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     */
    private function subscriptionQuery(array $filters): Builder
    {
        return Subscription::query()
            ->with(['product', 'rate.rate_type', 'user', 'transaction'])
            ->whereBetween('subscription_date', [$filters['startdate'], $filters['enddate']])
            ->when($filters['product'] !== [], function (Builder $query) use ($filters): void {
                $query->whereIn('product_id', $filters['product']);
            })
            ->when($filters['ratetype'] !== [], function (Builder $query) use ($filters): void {
                $query->whereHas('rate', function (Builder $rateQuery) use ($filters): void {
                    $rateQuery->whereIn('rate_type_id', $filters['ratetype']);
                });
            })
            ->when($filters['status'] === 'active', function (Builder $query): void {
                $query->where('status', 1);
            })
            ->when($filters['status'] === 'inactive', function (Builder $query): void {
                $query->where('status', '!=', 1);
            });
    }

    /**
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     * @return array<string, array<string, array<int, int|string>>>
     */
    private function chartData(array $filters): array
    {
        $dailySubscriptions = (clone $this->subscriptionQuery($filters))
            ->selectRaw('DATE(subscription_date) as report_date, COUNT(*) as total')
            ->groupByRaw('DATE(subscription_date)')
            ->orderByRaw('DATE(subscription_date)')
            ->pluck('total', 'report_date');

        $statusTotals = (clone $this->subscriptionQuery($filters))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'dailySubscriptions' => [
                'labels' => $dailySubscriptions->keys()->map(fn ($date): string => Carbon::parse($date)->format('M d'))->values()->all(),
                'data' => $dailySubscriptions->values()->map(fn ($total): int => (int) $total)->all(),
            ],
            'subscriptionStatus' => [
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
            'product' => array_map('intval', $validated['product'] ?? []),
            'ratetype' => array_map('intval', $validated['ratetype'] ?? []),
            'status' => $validated['status'] ?? null,
        ];
    }
}
