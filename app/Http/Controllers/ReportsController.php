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

        return view('modules.reportds.subscribers', $this->data);
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
