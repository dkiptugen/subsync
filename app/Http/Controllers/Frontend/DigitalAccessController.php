<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Subscription;
use App\Traits\Meta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DigitalAccessController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function authentication(): View
    {
        $this->data['products'] = $this->products();

        return view('modules.front.digital.auth', $this->data);
    }

    public function profile(Request $request): View
    {
        $this->data['user'] = $request->user()->load(['organization']);
        $this->data['subscriptions'] = Subscription::with(['product', 'rate', 'payment_method'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(8)
            ->get();

        return view('modules.front.digital.profile', $this->data);
    }

    public function payments(): View
    {
        $this->data['products'] = $this->products();
        $this->data['paymentMethods'] = PaymentMethod::where('status', 1)
            ->orderBy('name')
            ->get();

        return view('modules.front.digital.payments', $this->data);
    }

    private function products()
    {
        return Product::with(['rates' => function ($query): void {
            $query->where('status', 1)->orderBy('listorder')->orderBy('cost');
        }])
            ->where('status', 1)
            ->orderBy('product_name')
            ->get();
    }
}
