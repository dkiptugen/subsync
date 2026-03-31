<?php

    namespace App\Exports;

    use App\Models\User;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Log;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithHeadings;

    class SubscriberExport implements FromCollection,WithHeadings
        {
            public $startdate;
            public $enddate;

            public function __construct($request)
                {

                    $this->startdate = Carbon::parse($request->startdate)->toDateTimeString();
                    $this->enddate   = Carbon::parse($request->enddate)->toDateTimeString();
                }

        /**
         * @return \Illuminate\Support\Collection
         */
            public function collection()
                {

                    $user = User::with(['organization', 'providers'])
                                ->where(function($query){
                                    return $query->where('type','customer')
                                        ->orWhere('type','organization');
                                })
                                ->where('created_at', '>=',$this->startdate)
                                ->where('created_at', '<=',$this->enddate)
                                ->get();
                    return $user->map(function ($usr)
                        {

                            $data['name']         = $usr->name . ' ' . $usr->surname;
                            $data['email']        = $usr->email;
                            $data['organization'] = $usr->organization->name;
                            $data['status']       = $usr->status ? 'Active' : 'Inactive';
                            $data['phone']        = (string)$usr->phone;
                            $data['login_type']   = implode(', ', $usr->providers->pluck('provider')->toArray()??['Direct']);
                            $data['last_login'] = Carbon::parse($usr->last_login)->toDayDateTimeString();
                            $data['created_at'] = Carbon::parse($usr->created_at)->toDayDateTimeString();

                            return $data;
                        });



                }
            public function headings(): array
                {
                    return ['Name', 'Email',  'Organization', 'Status', 'Phone Number','Login Type', 'Last Login', 'Registration Date'];
                }
        }
