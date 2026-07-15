<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Meta;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function index()
    {
        $this->data['dashboard'] = [
            'metrics' => $this->metrics(),
            'revenue' => $this->revenueSnapshot(),
            'recentTransactions' => $this->recentTransactions(),
            'operationalHealth' => $this->operationalHealth(),
            'quickActions' => $this->quickActions(),
        ];

        return view('modules.dashboard.index', $this->data);
    }

    /**
     * @return array<int, array{label: string, value: string, helper: string, icon: string, tone: string, route: string}>
     */
    private function metrics(): array
    {
        $now = CarbonImmutable::now();

        return [
            [
                'label' => 'Active Subscriptions',
                'value' => number_format(Subscription::query()->where('status', 1)->count()),
                'helper' => number_format(Subscription::query()->whereBetween('expiry_date', [$now, $now->addDays(7)])->count()).' expiring in 7 days',
                'icon' => 'refresh-cw',
                'tone' => 'blue',
                'route' => route('subscription.index'),
            ],
            [
                'label' => 'Monthly Revenue',
                'value' => number_format((float) Transaction::query()
                    ->where('status', 1)
                    ->whereBetween('transaction_date', [$now->startOfMonth(), $now])
                    ->sum('amount_paid'), 2),
                'helper' => number_format(Transaction::query()->where('status', 1)->whereDate('transaction_date', $now)->count()).' paid today',
                'icon' => 'credit-card',
                'tone' => 'green',
                'route' => route('subscription.index'),
            ],
            [
                'label' => 'Products',
                'value' => number_format(Product::query()->count()),
                'helper' => number_format(Product::query()->where('status', 1)->count()).' active products',
                'icon' => 'box',
                'tone' => 'amber',
                'route' => route('product.index'),
            ],
            [
                'label' => 'Subscribers',
                'value' => number_format(User::query()->where('type', 'customer')->count()),
                'helper' => number_format(User::query()->where('status', 1)->count()).' active accounts',
                'icon' => 'users',
                'tone' => 'slate',
                'route' => route('user.index'),
            ],
        ];
    }

    /**
     * @return array{paid: float, pending: int, failed: int, approvals: int}
     */
    private function revenueSnapshot(): array
    {
        $now = CarbonImmutable::now();

        return [
            'paid' => (float) Transaction::query()
                ->where('status', 1)
                ->whereBetween('transaction_date', [$now->startOfMonth(), $now])
                ->sum('amount_paid'),
            'pending' => Transaction::query()->where('status', 0)->count(),
            'failed' => Transaction::query()->where('status', 2)->count(),
            'approvals' => Subscription::query()
                ->where(static function ($query): void {
                    $query->whereNull('finance_approval_status')
                        ->orWhere('finance_approval_status', 0);
                })
                ->count(),
        ];
    }

    /**
     * @return Collection<int, Transaction>
     */
    private function recentTransactions(): Collection
    {
        return Transaction::query()
            ->with(['payment_method:id,name', 'user:id,name,email'])
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'identifier', 'payment_method_id', 'receipt', 'amount_paid', 'currency', 'status', 'user_id', 'created_at']);
    }

    /**
     * @return array<int, array{label: string, value: string, detail: string, icon: string}>
     */
    private function operationalHealth(): array
    {
        return [
            [
                'label' => 'Organizations',
                'value' => number_format(Organization::query()->count()),
                'detail' => number_format(Organization::query()->where('status', 1)->count()).' active',
                'icon' => 'briefcase',
            ],
            [
                'label' => 'Leads',
                'value' => number_format(Lead::query()->count()),
                'detail' => number_format(Lead::query()->whereDate('created_at', CarbonImmutable::today())->count()).' captured today',
                'icon' => 'target',
            ],
            [
                'label' => 'Transactions',
                'value' => number_format(Transaction::query()->count()),
                'detail' => number_format(Transaction::query()->where('status', 1)->count()).' successful',
                'icon' => 'activity',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, route: string, icon: string}>
     */
    private function quickActions(): array
    {
        return [
            ['label' => 'Add Product', 'route' => route('product.create'), 'icon' => 'plus-square'],
            ['label' => 'New Subscription', 'route' => route('subscription.create'), 'icon' => 'file-plus'],
            ['label' => 'Review Approvals', 'route' => route('subscription-approval.index'), 'icon' => 'check-square'],
            ['label' => 'Export Subscribers', 'route' => route('user.export_view'), 'icon' => 'download'],
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
