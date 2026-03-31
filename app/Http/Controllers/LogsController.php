<?php

    namespace App\Http\Controllers;

    use App\Models\Activity;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;

    class LogsController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function index($user)
                {
                    $this->data['user'] = $user;
                    return view('modules.logs.index' ,$this->data);
                }

        /**
         * @param Request $request
         * @param int     $user
         *
         * @return JsonResponse
         */
            public function get(Request $request ,int $user = 0)
            : \Illuminate\Http\JsonResponse
                {

                    $columns = [
                        'id' ,'description' ,'causer_id' ,'subject_type' ,'subject_id' ,'properties' ,'created_at'

                    ];
                    $activity = Activity::query();
                    $activity->when($user != 0 ,function ($query) use ($user)
                        {
                            return $query->where('causer_id' ,$user);
                        });
                    $totalData = $activity->count();
                    $totalFiltered = $totalData;
                    $limit = $request->input('length');
                    $start = $request->input('start');
                    $order = $columns[$request->input('order.0.column')];
                    $dir = $request->input('order.0.dir');
                    if (empty($request->input('search.value')))
                        {
                            $posts = $activity->offset($start)
                                              ->limit($limit)
                                              ->orderBy($order ,$dir)
                                              ->get();
                        }
                    else
                        {
                            $search = $request->input('search.value');
                            $activity = Activity::query();
                            $activity->when($user != 0 ,function ($query) use ($user)
                                {
                                    return $query->where('causer_id' ,$user);
                                });
                            $activity->whereHas("user" ,function ($subquery) use ($search)
                                {
                                    $subquery->where('name' ,'LIKE' ,"%{$search}%");
                                })
                                     ->orWhere('description' ,'LIKE' ,"%{$search}%")
                                     ->orWhere('subject_type' ,'LIKE' ,"%{$search}%")
                                     ->orWhere('subject_id' ,'LIKE' ,"%{$search}%")
                                     ->orWhere('properties' ,'LIKE' ,"%{$search}%");
                            $posts = $activity->offset($start)
                                              ->limit($limit)
                                              ->orderBy($order ,$dir)
                                              ->get();

                            $totalFiltered = $activity->count();
                        }
                    $data = [];
                    if (!empty($posts))
                        {
                            $pos = $start + 1;
                            foreach ($posts as $post)
                                {

                                    $nestedData['pos'] = $pos;
                                    $nestedData['action'] = $post->description;
                                    $nestedData['executer'] = '<a href="' . route('logs.user.index' ,$post->user->id ?? 0) . '">' . $post->user->name . "</a";
                                    $nestedData['model'] = $post->subject_type;
                                    $nestedData['affectedid'] = $post->subject_id;
                                    $nestedData['change'] = $post->properties;
                                    $nestedData['time'] = $post->created_at->format('h:ia d-m-Y');

                                    $data[] = $nestedData;
                                    $pos++;
                                }
                        }

                    $json_data = [
                        "draw" => (int)$request->input('draw') ,"recordsTotal" => $totalData ,"recordsFiltered" => $totalFiltered ,"data" => $data
                    ];
                    return response()->json($json_data);
                }

            public function export_view()
                {
                    return view('modules.logs.index' ,$this->data);
                }

            public function export(Request $request)
                {

                }
        }
