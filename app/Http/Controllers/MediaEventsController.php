<?php

namespace App\Http\Controllers;

use App\Models\MediaEvent;
use App\Traits\Meta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaEventsController extends Controller
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
        return view('modules.mediaevents.index', $this->data);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = MediaEvent::query();

        return response()->json($this->datatableResponse(
            request: $request,
            query: $query,
            searchable: function (Builder $searchQuery, string $search): void {
                $searchQuery->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('identifier', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%");
                });
            },
            orderColumns: [
                1 => 'name',
                2 => 'identifier',
                3 => 'status',
                4 => 'created_at',
            ],
            defaultOrder: 'created_at',
            rowMapper: function (MediaEvent $event, int $position): array {
                return [
                    'pos' => $position,
                    'name' => e($event->name),
                    'identifier' => e($event->identifier),
                    'status' => e(ucfirst($event->status)),
                    'created_at' => $event->created_at?->format('M d, Y H:i') ?? '-',
                    'actions' => view('partials._table-actions', [
                        'editUrl' => route('media_events.edit', $event),
                        'deleteUrl' => route('media_events.destroy', $event),
                        'deleteTarget' => $event->name,
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
        return view('modules.mediaevents.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'identifier' => 'required|unique:media_events,identifier',
            'status' => 'required|string',

        ]);

        MediaEvent::create([
            'name' => $request->name,
            'identifier' => $request->identifier,
            'status' => $request->status,

        ]);

        return self::success('MediaEvents', 'Event entry added successfully', route('media_events.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mediaevents = MediaEvent::findOrFail($id);
        $this->data['record'] = $mediaevents;

        return view('modules.mediaevents.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'identifier' => 'required|unique:media_events,identifier',
            'status' => 'required|string',

        ]);

        $mediaevent = MediaEvent::findOrFail($id);

        $mediaevent->update([

            'name' => $request->name,
            'identifier' => $request->identifier,
            'status' => $request->status,
        ]);

        return self::success('MediaEvents', 'Events entry updated successfully', route('media_events.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        MediaEvent::where('id', $id)->delete();

        return redirect()->back()->with(['status' => true, 'msg' => 'MediaEvents entry deleted successfully', 'header' => 'MediaEvents']);
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
