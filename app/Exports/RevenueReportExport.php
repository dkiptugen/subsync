<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RevenueReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  array{startdate: Carbon, enddate: Carbon, product: array<int>, ratetype: array<int>, status: string|null}  $filters
     */
    public function __construct(private readonly array $filters) {}

    public function collection()
    {
        return Transaction::query()
            ->with(['payment_method', 'subscription.product', 'subscription.rate.rate_type', 'user'])
            ->whereBetween('transaction_date', [$this->filters['startdate'], $this->filters['enddate']])
            ->when($this->filters['product'] !== [], function ($query): void {
                $query->whereHas('subscription', function ($subscriptionQuery): void {
                    $subscriptionQuery->whereIn('product_id', $this->filters['product']);
                });
            })
            ->when($this->filters['ratetype'] !== [], function ($query): void {
                $query->whereHas('subscription.rate', function ($rateQuery): void {
                    $rateQuery->whereIn('rate_type_id', $this->filters['ratetype']);
                });
            })
            ->when($this->filters['status'] === 'active', function ($query): void {
                $query->where('status', 1);
            })
            ->when($this->filters['status'] === 'inactive', function ($query): void {
                $query->where('status', '!=', 1);
            })
            ->latest('transaction_date')
            ->get()
            ->map(function (Transaction $transaction): array {
                return [
                    'identifier' => $transaction->identifier,
                    'receipt' => $transaction->receipt ?? '-',
                    'subscriber' => trim(($transaction->user?->name ?? '').' '.($transaction->user?->surname ?? '')),
                    'email' => $transaction->user?->email ?? '-',
                    'product' => $transaction->subscription?->product?->product_name ?? '-',
                    'subscription_type' => $transaction->subscription?->rate?->rate_type?->name ?? '-',
                    'channel' => $transaction->channel ?? $transaction->payment_method?->name ?? '-',
                    'currency' => $transaction->currency ?? '-',
                    'amount_paid' => $transaction->amount_paid,
                    'transaction_date' => $transaction->transaction_date
                        ? Carbon::parse($transaction->transaction_date)->toDateTimeString()
                        : null,
                    'status' => $transaction->status ? 'Successful' : 'Failed',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Identifier',
            'Receipt',
            'Subscriber',
            'Email',
            'Product',
            'Subscription Type',
            'Channel',
            'Currency',
            'Amount Paid',
            'Transaction Date',
            'Status',
        ];
    }
}
