<?php

    namespace App\Http\Controllers;

    use App\Models\Campaign;
    use Illuminate\Http\Request;

    class CampaignController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
         */
            public function index()
                {
                    return view('modules.campaign.index' ,$this->data);
                }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
         */
            public function create()
                {
                    return view('modules.campaign.add' ,$this->data);
                }

        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
            public function store(Request $request)
                {
                    //
                }

        /**
         * Display the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
         */
            public function show($id)
                {
                    $this->data['campaign'] = Campaign::find($id);
                    return view('modules.campaign.show' ,$this->data);
                }

        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
         */
            public function edit($id)
                {
                    $this->data['campaign'] = Campaign::find($id);
                    return view('modules.campaign.edit' ,$this->data);
                }

        /**
         * Update the specified resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @param int                      $id
         *
         * @return \Illuminate\Http\Response
         */
            public function update(Request $request ,$id)
                {
                    //
                }

        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         *
         * @return \Illuminate\Http\Response
         */
            public function destroy($id)
                {
                    //
                }

        /**
         * @param Request $request
         *
         * @return \Illuminate\Http\JsonResponse
         */
            public function get(Request $request)
                {
                    return response()->json([]);
                }
        }
