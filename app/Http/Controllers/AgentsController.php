<?php

namespace App\Http\Controllers;

use App\Jobs\ImportAgents;
use App\Models\Agent;
use App\Models\Organization;
use App\Traits\Meta;
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
