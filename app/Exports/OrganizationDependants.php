<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrganizationDependants implements FromCollection, WithHeadings
    {
        public $orgid;
        public function __construct($orgid)
            {
                $this->orgid = $orgid;
            }
        /**
        * @return \Illuminate\Support\Collection
        */
        public function collection()
        {
            $user = User::where('organization_id',$this->orgid)->get();
            $data = [];

            return $user->map(function ($usr) use($data)
                {
                    $data['name']         = $usr->name . ' ' . $usr->surname;
                    $data['email']        = $usr->email;
                    $data['last_login'] =$usr->last_login;
                    $data['status']       = $usr->status ? 'Active' : 'Inactive';
                    $data['phone']        = (string)$usr->phone;
                    return $data;
                });
        }
            public function headings(): array
                {
                    return ['Name', 'Email',  'Last Login', 'Status', 'Phone Number'];
                }
    }
