<?php

namespace App\Http\Controllers;

use App\Jobs\ImportAgents;
use App\Models\Agent;
use App\Models\Organization;
use App\Traits\Meta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class AgentsController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function index()
    {
        return view('modules.agents.index', $this->data);
    }

    public function datatable(Request $request): JsonResponse
    {
        $columns = ['id', 'name', 'email', 'phone', 'type', 'department', 'country'];
        $limit = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);
        $orderColumn = (int) $request->input('order.0.column', 1);
        $order = $columns[$orderColumn] ?? 'name';
        $dir = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        $search = $request->input('search.value');

        $query = Agent::with('organizations:id,name');
        $totalData = Agent::count();

        if (! empty($search)) {
            $query->where(function ($agentQuery) use ($search): void {
                $agentQuery->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%")
                    ->orWhere('department', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhereHas('organizations', function ($organizationQuery) use ($search): void {
                        $organizationQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $totalFiltered = (clone $query)->count();
        $agents = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($agents as $index => $agent) {
            $data[] = [
                'pos' => $start + $index + 1,
                'name' => $agent->name ?? '',
                'email' => $agent->email ?? '',
                'phone' => $agent->phone ?? '',
                'type' => ucfirst((string) $agent->type),
                'department' => $agent->department ?? '',
                'country' => $agent->country ?? '',
                'organizations' => $agent->organizations->pluck('name')->join(', '),
                'action' => self::button_generate('agents', $agent->id, [], ['show']),
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    public function create()
    {

        $this->data['types'] = ['staff', 'external'];
        $this->data['organizations'] = Organization::orderBy('name')->get(['id', 'name']);

        return view('modules.agents.create', $this->data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:agents',
            'organizations' => 'nullable|array',
            'organizations.*' => 'exists:organizations,id',
        ]);
        if ($validator->fails()) {
            return self::failed('Sales Agents', $validator->errors()->first(), route('agents.create'));
        }

        $agent = Agent::create([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'phone' => $request->phone,
            'department' => $request->department,
            'country' => $request->country,
        ]);
        $agent->organizations()->sync($request->input('organizations', []));

        return self::success('Sales Agents', 'Agent added successfully', route('agents.index'));
    }

    public function show(Agent $agent) {}

    public function edit(Agent $agent)
    {

        $this->data['agent'] = $agent->load('organizations');
        $this->data['types'] = ['staff', 'external'];
        $this->data['organizations'] = Organization::orderBy('name')->get(['id', 'name']);

        return view('modules.agents.edit', $this->data);
    }

    public function update(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:agents,email,'.$agent->id,
            'organizations' => 'nullable|array',
            'organizations.*' => 'exists:organizations,id',
        ]);
        if ($validator->fails()) {
            return self::failed('Sales Agents', $validator->errors()->first(), route('agents.edit', $agent));
        }

        $agent->update([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'phone' => $request->phone,
            'department' => $request->department,
            'country' => $request->country,
        ]);
        $agent->organizations()->sync($request->input('organizations', []));

        return self::success('Sales Agents', 'Agent updated successfully', route('agents.index'));
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();

        return redirect()->route('agents.index');
    }

    public function import(Request $request)
    {
        return view('modules.agents.import', $this->data);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

        } catch (ValidationException $e) {
            return self::failed('Sales Agents', $e->validator->errors()->first(), route('agents.import'));
        }

        $data = Excel::toCollection(null, $request->file('excel_file'));

        ImportAgents::dispatch($data)->onQueue('low');

        return self::success('Sales Agents', 'Agent import started', route('agents.index'));
    }
}
