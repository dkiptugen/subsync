<?php

namespace App\Exports;

use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubscriptionReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     */
    public function __construct(private readonly array $filters) {}

    public function collection()
    {
        return Subscription::query()
            ->with(['product', 'rate.rate_type', 'user', 'transaction'])
            ->whereBetween('subscription_date', [$this->filters['startdate'], $this->filters['enddate']])
            ->when($this->filters['product'] !== [], function ($query): void {
                $query->whereIn('product_id', $this->filters['product']);
            })
            ->when($this->filters['ratetype'] !== [], function ($query): void {
                $query->whereHas('rate', function ($rateQuery): void {
                    $rateQuery->whereIn('rate_type_id', $this->filters['ratetype']);
                });
            })
            ->when($this->filters['status'] === 'active', function ($query): void {
                $query->where('status', 1);
            })
            ->when($this->filters['status'] === 'inactive', function ($query): void {
                $query->where('status', '!=', 1);
            })
            ->latest('subscription_date')
            ->get()
            ->map(function (Subscription $subscription): array {
                $transaction = $subscription->transaction->first();

                return [
                    'identifier' => $subscription->identifier,
                    'product' => $subscription->product?->product_name ?? '-',
                    'subscription_type' => $subscription->rate?->rate_type?->name ?? '-',
                    'rate' => $subscription->rate?->name ?? '-',
                    'cost' => $subscription->rate?->cost,
                    'currency' => $subscription->rate?->currency,
                    'amount_paid' => $subscription->transaction->sum('amount_paid'),
                    'receipt' => $transaction?->receipt ?? '-',
                    'recurrent' => $subscription->reccuring ? 'Yes' : 'No',
                    'subscription_date' => $subscription->subscription_date
                        ? Carbon::parse($subscription->subscription_date)->toDateTimeString()
                        : null,
                    'expiry_date' => $subscription->expiry_date
                        ? Carbon::parse($subscription->expiry_date)->toDateTimeString()
                        : null,
                    'category' => $subscription->category ?? '-',
                    'subscriber' => trim(($subscription->user?->name ?? '').' '.($subscription->user?->surname ?? '')),
                    'email' => $subscription->user?->email ?? '-',
                    'status' => $subscription->status ? 'Active' : 'Inactive',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Identifier',
            'Product',
            'Subscription Type',
            'Rate',
            'Cost',
            'Currency',
            'Amount Paid',
            'Receipt',
            'Recurrent',
            'Subscription Date',
            'Expiry Date',
            'Category',
            'Subscriber',
            'Email',
            'Status',
        ];
    }
}
