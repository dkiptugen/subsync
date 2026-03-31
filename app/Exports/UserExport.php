<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
    {

    /**
     * @return \Illuminate\Support\Collection
     */
        public function collection()
            {
                $user = User::where('type','owner')->with(['organization', 'role'])->get();
                $data = [];

                return $user->map(function ($usr)
                    {
                        $data['name']         = $usr->name . ' ' . $usr->surname;
                        $data['email']        = $usr->email;
                        $data['role']         = $usr->role->name;
                        $data['organization'] = $usr->organization->name;
                        $data['status']       = $usr->status ? 'Active' : 'Inactive';
                        $data['phone']        = (string)$usr->phone;
                        return $data;
                    });
            }

        public function headings(): array
            {
                return ['Name', 'Email', 'Role', 'Organization', 'Status', 'Phone Number'];
            }


    }
