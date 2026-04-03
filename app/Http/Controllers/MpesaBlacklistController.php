<?php

namespace App\Http\Controllers;

use App\Models\MpesaBlacklist;
use App\Traits\Meta;
use Illuminate\Http\Request;

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
    public function index()
    {
        //
        return view('modules.blacklist.index', $this->data);
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
        //
        MpesaBlacklist::where('id', $id)->delete();

        return redirect()->back()->with(['status' => true, 'msg' => 'Blacklist entry deleted successfully', 'header' => 'Blacklist']);

        return self::success('Blacklist', 'Blacklist entry deleted successfully', route('mpesa_blacklist.index'));

    }
}
