<?php

namespace App\Http\Controllers;

use App\Models\MpesaBlacklist;
use App\Traits\Meta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MpesaBlacklistController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('modules.blacklist.index', $this->data);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = MpesaBlacklist::query();

        return response()->json($this->datatableResponse(
            request: $request,
            query: $query,
            searchable: function (Builder $searchQuery, string $search): void {
                $searchQuery->where(function (Builder $query) use ($search): void {
                    $query->where('phone', 'LIKE', "%{$search}%")
                        ->orWhere('type', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            },
            orderColumns: [
                1 => 'phone',
                2 => 'type',
                4 => 'created_at',
            ],
            defaultOrder: 'created_at',
            rowMapper: function (MpesaBlacklist $blacklist, int $position): array {
                return [
                    'pos' => $position,
                    'phone' => e($blacklist->phone),
                    'type' => e($blacklist->type ?? '-'),
                    'description' => e($blacklist->description ?? '-'),
                    'created_at' => $blacklist->created_at?->format('M d, Y H:i') ?? '-',
                    'actions' => view('partials._table-actions', [
                        'editUrl' => route('mpesa_blacklist.edit', $blacklist),
                        'deleteUrl' => route('mpesa_blacklist.destroy', $blacklist),
                        'deleteTarget' => $blacklist->phone,
                    ])->render(),
                ];
            }
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modules.blacklist.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'phone_number' => 'required|unique:mpesa_blacklists,phone',
        ]);

        MpesaBlacklist::create([
            'phone' => $request->phone_number,
            'description' => $request->reason,
        ]);

        return self::success('Blacklist', 'Blacklist entry added successfully', route('mpesa_blacklist.index'));
    }

    public function edit(string $id)
    {
        $blacklist = MpesaBlacklist::findOrFail($id);
        $this->data['record'] = $blacklist;

        return view('modules.blacklist.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'phone_number' => 'required|unique:mpesa_blacklists,phone,'.$id,
            'reason' => 'nullable|string|max:255',
        ]);

        $blacklist = MpesaBlacklist::findOrFail($id);

        $blacklist->update([
            'phone' => $request->phone_number,
            'description' => $request->reason,
        ]);

        return self::success('Blacklist', 'Blacklist entry updated successfully', route('mpesa_blacklist.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        MpesaBlacklist::where('id', $id)->delete();

        return redirect()->back()->with(['status' => true, 'msg' => 'Blacklist entry deleted successfully', 'header' => 'Blacklist']);
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
}
