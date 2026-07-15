<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection
     */
    public function collection()
    {
        $user = User::where('type', 'owner')->with(['organization', 'roles'])->get();

        return $user->map(function ($usr) {
            $data['name'] = $usr->name.' '.$usr->surname;
            $data['email'] = $usr->email;
            $data['role'] = $usr->roles->first()?->name ?? 'None';
            $data['organization'] = $usr->organization->name;
            $data['status'] = $usr->status ? 'Active' : 'Inactive';
            $data['phone'] = (string) $usr->phone;

            return $data;
        });
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Role', 'Organization', 'Status', 'Phone Number'];
    }
}
