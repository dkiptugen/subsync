<?php

namespace App\Utils;

use App\Models\Package;
use App\Models\Subscription;
use App\Models\Transaction;
use DateInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class Sdata
{
    public static function Subcheck($subid, $amount)
    {
        $checksub = Subscription::where('id', $subid)->first();
        if (! is_null($checksub)) {
            $value = new stdClass;
            $days = Package::where('id', $checksub->package_id)->first();
            $d = ($days->period / $days->amount) * $amount;

            if ($checksub->status == 1 && (date_create($checksub->expiry_date) > date_create('now'))) {
                $value->expiry = date_create($checksub->expiry_date)->add(new DateInterval('P'.ceil($d).'D'));
                $value->status = (ceil($d) > 1) ? 1 : 0;
            } else {
                $value->expiry = date_create('now')->add(new DateInterval('P'.ceil($d).'D'));
                $value->status = (ceil($d) > 1) ? 1 : 0;
            }

            return $value;
        }
    }

    public static function getaccess($perm)
    {
        $role = DB::table('roles')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('role_has_permissions.permission_id', $perm)
            ->select('roles.name')
            ->get()
            ->toArray();

        return implode(',', array_column($role, 'name'));
    }

    public static function getperm($roleid)
    {
        $perm = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $roleid)
            ->select('permissions.name')
            ->get();
        $x = "<div class='d-flex flex-wrap'>";
        foreach ($perm as $value) {
            $x .= '<a href=""><span class="badge badge-nation m-1">'.$value->name.'</span></a>';
        }
        $x .= '</div>';

        return $x;

    }

    public static function checkaccess($roleid, $permid)
    {
        return DB::table('role_has_permissions')
            ->where('role_id', $roleid)
            ->where('permission_id', $permid)
            ->exists();
    }

    public static function mpesa($trans_code, $amount_paid, $receipt_no, $user_name, $user_number, $transtime, $response)
    {
        $transaction = Transaction::with(['subscription'])->where('identifier', $trans_code)->first();
        if ($transaction->amount <= $amount_paid) {
            $transaction->amount_paid = $amount_paid;
            $transaction->status = 1;
            $transaction->receipt = $receipt_no;
            $transaction->initiator = $user_name.' - '.$user_number;
            $transaction->response = $response;
            $transaction->transaction_date = Carbon::parse($transtime)->toDateTimeString();
            $transaction->save();
            $transaction->subscription()->update(['status' => 1]);

        } else {
            $transaction->decrement('amount', $amount_paid);
            $transaction->amount_paid = $amount_paid;
            $transaction->receipt = $receipt_no;
            $transaction->initiator = $user_name.' - '.$user_number;
            $transaction->response = $response;
            $transaction->transaction_date = Carbon::parse($transtime)->toDateTimeString();
            $transaction->save();
        }
    }
}
