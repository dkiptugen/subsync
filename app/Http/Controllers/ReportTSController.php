<?php

namespace App\Http\Controllers;

use App\Exports\AccountReportExport;
use App\Exports\RevenueReportExport;
use App\Exports\SubscriberExport;
use App\Exports\SubscriptionReportExport;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Product;
use App\Models\RateType;
use App\Models\Subscription;
use App\Models\Transaction;
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

    public function accounts_form(ReportFilterRequest $request): View
    {
        return $this->accountReportView($request, false);
    }

    public function accounts(ReportFilterRequest $request)
    {
        $filters = $this->filters($request);

        return Excel::download(
            new AccountReportExport($filters),
            'individual-accounts-'.$filters['startdate']->toDateString().'-'.$filters['enddate']->toDateString().'.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function activated_accounts_form(ReportFilterRequest $request): View
    {
        return $this->accountReportView($request, true);
    }

    public function activated_accounts(ReportFilterRequest $request)
    {
        $filters = $this->filters($request);

        return Excel::download(
            new AccountReportExport($filters, true),
            'activated-accounts-'.$filters['startdate']->toDateString().'-'.$filters['enddate']->toDateString().'.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function revenue_form(ReportFilterRequest $request): View
    {
        $filters = $this->filters($request);

        $this->data['products'] = Product::orderBy('product_name')->get();
        $this->data['rateTypes'] = RateType::orderBy('name')->get();
        $this->data['transactions'] = $this->revenueQuery($filters)
            ->latest('transaction_date')
            ->paginate(25)
            ->withQueryString();
        $this->data['filters'] = $filters;
        $this->data['chartData'] = $this->revenueChartData($filters);

        return view('modules.reportds.revenue', $this->data);
    }

    public function revenue(ReportFilterRequest $request)
    {
        $filters = $this->filters($request);

        return Excel::download(
            new RevenueReportExport($filters),
            'individual-revenue-'.$filters['startdate']->toDateString().'-'.$filters['enddate']->toDateString().'.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    private function accountReportView(ReportFilterRequest $request, bool $activatedOnly): View
    {
        $filters = $this->filters($request);

        $this->data['products'] = Product::orderBy('product_name')->get();
        $this->data['rateTypes'] = RateType::orderBy('name')->get();
        $this->data['subscriptions'] = $this->accountQuery($filters, $activatedOnly)
            ->latest('subscription_date')
            ->paginate(25)
            ->withQueryString();
        $this->data['filters'] = $filters;
        $this->data['activatedOnly'] = $activatedOnly;
        $this->data['chartData'] = $this->accountChartData($filters, $activatedOnly);

        return view('modules.reportds.accounts', $this->data);
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
     */
    private function accountQuery(array $filters, bool $activatedOnly): Builder
    {
        return $this->subscriptionQuery($filters)
            ->with(['activator'])
            ->when($activatedOnly, function (Builder $query): void {
                $query->where('activator_id', '!=', 0);
            });
    }

    /**
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     */
    private function revenueQuery(array $filters): Builder
    {
        return Transaction::query()
            ->with(['payment_method', 'subscription.product', 'subscription.rate.rate_type', 'user'])
            ->whereBetween('transaction_date', [$filters['startdate'], $filters['enddate']])
            ->when($filters['product'] !== [], function (Builder $query) use ($filters): void {
                $query->whereHas('subscription', function (Builder $subscriptionQuery) use ($filters): void {
                    $subscriptionQuery->whereIn('product_id', $filters['product']);
                });
            })
            ->when($filters['ratetype'] !== [], function (Builder $query) use ($filters): void {
                $query->whereHas('subscription.rate', function (Builder $rateQuery) use ($filters): void {
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
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     * @return array<string, array<string, array<int, int|string>>>
     */
    private function accountChartData(array $filters, bool $activatedOnly): array
    {
        $dailyAccounts = (clone $this->accountQuery($filters, $activatedOnly))
            ->selectRaw('DATE(subscription_date) as report_date, COUNT(*) as total')
            ->groupByRaw('DATE(subscription_date)')
            ->orderByRaw('DATE(subscription_date)')
            ->pluck('total', 'report_date');

        $statusTotals = (clone $this->accountQuery($filters, $activatedOnly))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'dailyAccounts' => [
                'labels' => $dailyAccounts->keys()->map(fn ($date): string => Carbon::parse($date)->format('M d'))->values()->all(),
                'data' => $dailyAccounts->values()->map(fn ($total): int => (int) $total)->all(),
            ],
            'accountStatus' => [
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
     * @return array<string, array<string, array<int, int|string>>>
     */
    private function revenueChartData(array $filters): array
    {
        $dailyRevenue = (clone $this->revenueQuery($filters))
            ->selectRaw('DATE(transaction_date) as report_date, COALESCE(SUM(amount_paid), 0) as total')
            ->groupByRaw('DATE(transaction_date)')
            ->orderByRaw('DATE(transaction_date)')
            ->pluck('total', 'report_date');

        $channelRevenue = (clone $this->revenueQuery($filters))
            ->selectRaw("COALESCE(NULLIF(channel, ''), 'Unknown') as channel_name, COALESCE(SUM(amount_paid), 0) as total")
            ->groupByRaw("COALESCE(NULLIF(channel, ''), 'Unknown')")
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'channel_name');

        return [
            'dailyRevenue' => [
                'labels' => $dailyRevenue->keys()->map(fn ($date): string => Carbon::parse($date)->format('M d'))->values()->all(),
                'data' => $dailyRevenue->values()->map(fn ($total): float => (float) $total)->all(),
            ],
            'channelRevenue' => [
                'labels' => $channelRevenue->keys()->values()->all(),
                'data' => $channelRevenue->values()->map(fn ($total): float => (float) $total)->all(),
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
