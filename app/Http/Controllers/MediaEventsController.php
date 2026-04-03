<?php

namespace App\Http\Controllers;

use App\Models\MediaEvent;
use App\Traits\Meta;
use Illuminate\Http\Request;

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
    public function index()
    {
        return view('modules.mediaevents.index', $this->data);
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

        return self::success('MediaEvents', 'Events entry deleted successfully', route('media_events.index'));
    }
}
