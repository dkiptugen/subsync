<?php

namespace App\Services;

use App\Events\DashboardUpdated;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\DashboardActivityNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardRealtimeService
{
    /**
     * @var array<class-string<Model>, array<int, string>>
     */
    private const RELEVANT_UPDATES = [
        Transaction::class => ['status', 'amount_paid', 'transaction_date', 'receipt'],
        Subscription::class => ['status', 'expiry_date', 'finance_approval_status'],
        Product::class => ['status'],
        User::class => ['status', 'type'],
        Organization::class => ['status'],
        Lead::class => [],
    ];

    public function publish(Model $model, string $action): void
    {
        if (! $this->isRelevant($model, $action)) {
            return;
        }

        DashboardUpdated::dispatch(Str::kebab(class_basename($model)), $action);

        $notification = $this->notificationFor($model, $action);
        if ($notification !== null) {
            $this->recipients()->each(
                fn (User $recipient) => $recipient->notify($notification->forDelivery())
            );
        }
    }

    private function isRelevant(Model $model, string $action): bool
    {
        if ($action !== 'updated') {
            return true;
        }

        $columns = self::RELEVANT_UPDATES[$model::class] ?? [];

        return $columns !== [] && $model->wasChanged($columns);
    }

    private function notificationFor(Model $model, string $action): ?DashboardActivityNotification
    {
        return match (true) {
            $model instanceof Transaction && $action === 'created' => new DashboardActivityNotification(
                'Payment initiated',
                'Transaction '.($model->identifier ?: $model->getKey()).' is awaiting payment.',
                'credit-card',
                'primary',
                route('subscription.index'),
            ),
            $model instanceof Transaction && $action === 'updated' && $model->wasChanged('status') && (int) $model->status === 1 => new DashboardActivityNotification(
                'Payment received',
                'Transaction '.($model->receipt ?: $model->identifier ?: $model->getKey()).' was paid successfully.',
                'check-circle',
                'success',
                route('subscription.index'),
            ),
            $model instanceof Transaction && $action === 'updated' && $model->wasChanged('status') && (int) $model->status === 2 => new DashboardActivityNotification(
                'Payment failed',
                'Transaction '.($model->identifier ?: $model->getKey()).' failed and needs attention.',
                'alert-circle',
                'danger',
                route('subscription.index'),
            ),
            $model instanceof Subscription && $action === 'created' => new DashboardActivityNotification(
                'Subscription created',
                'Subscription '.($model->identifier ?: $model->getKey()).' was added.',
                'refresh-cw',
                'success',
                route('subscription.index'),
            ),
            $model instanceof User && $action === 'created' && $model->type === 'customer' => new DashboardActivityNotification(
                'New subscriber',
                ($model->name ?: $model->email ?: 'A customer').' joined the platform.',
                'user-plus',
                'primary',
                route('user.index'),
            ),
            $model instanceof Lead && $action === 'created' => new DashboardActivityNotification(
                'New lead captured',
                'A new lead is ready for follow-up.',
                'target',
                'warning',
                route('lead.index'),
            ),
            default => null,
        };
    }

    /**
     * @return Collection<int, User>
     */
    private function recipients(): Collection
    {
        return User::query()
            ->where('status', 1)
            ->where(static function ($query): void {
                $query->where('type', 'owner')
                    ->orWhereHas('roles', static fn ($roleQuery) => $roleQuery->where('name', 'Super Admin'));
            })
            ->get();
    }
}
