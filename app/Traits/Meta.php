<?php

namespace App\Traits;

use AmrShawky\Currency;
use App\Models\Coupon;
use App\Models\CurrencyConvertor;
use App\Models\Rate;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PermissionHelper;
use DateInterval;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

trait Meta
{
    public static $elements = ['edit' => 'fas fa-edit', 'show' => 'fas fa-eye', 'destroy' => 'fas fa-trash'];

    public static function site_def(): array
    {

        return [
            'name' => 'Radio Africa Group',
            'title' => 'Unified subscription',
            'description' => 'Unified subscription',
            'logo' => asset('assets/img/logo-dark.png'),
            'image' => asset('assets/img/logo.png'),
            'keywords' => 'The Star, Radio Africa Group, Mpasho,Ticket Yetu',
            'author' => 'Radio Africa Group',
        ];

    }

    public static function success($title, $message, $redirecturl = ''): array
    {

        return [
            'status' => true,
            'msg' => $message,
            'header' => $title,
            'url' => $redirecturl,
        ];
    }

    public static function failed($title, $message, $redirecturl = ''): array
    {

        return [
            'status' => false,
            'msg' => $message,
            'header' => $title,
            'url' => $redirecturl,
        ];
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public static function time_ago($datetime, $full = false)
    {

        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k.' '.$v.($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (! $full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string).' ago' : 'just now';
    }

    /**
     * @return DateTime|false
     *
     * @throws \Exception
     */
    public static function expiry_calc($product_id, $amount, $expirydate = null)
    {

        $days = Rate::where('product_id', $product_id)
            ->where('amount', '>=', $amount)
            ->limit(1)
            ->first();
        if (is_null($days)) {
            $days = Rate::where('product_id', $product_id)
                ->where('amount', '<=', $amount)
                ->orderBy('amount', 'DESC')
                ->limit(1)
                ->first();
        }
        $d = ($days->duration / $days->amount) * $amount;
        if ($expirydate !== null) {
            return date_create($expirydate)->add(new DateInterval('P'.ceil($d).'D'));
        }

        return date_create(now())->add(new DateInterval('P'.ceil($d).'D'));
    }

    /**
     * @param  $type
     * @param  $val
     * @return void
     */
    public static function setEnv($key, $value)
    {

        $value = '"'.trim($value).'"';
        file_put_contents(app()->environmentFilePath(),
            str_replace($key.'="'.env($key).'"', $key.'='.$value, file_get_contents(app()->environmentFilePath()))
        );

    }

    /**
     * @return string
     */
    public static function button_generate($key, $id, $elements = [], $remove = [])
    {

        $ele = self::$elements;
        foreach ($remove as $rem) {
            unset($ele[$rem]);
        }
        if (is_array($id)) {
            $i = $id[count($id) - 1];
        } else {
            $i = $id;
        }
        $btn = '';
        foreach (array_merge($ele, $elements) as $k => $value) {
            if (PermissionHelper::canAccess($key.'.'.$k)) {
                if ($k != 'destroy') {
                    $btn .= '<a href="'.route($key.'.'.$k, $id).'" class="text text-dark mr-2" id="'.$k.'-'.$i.'"><i class="'.$value.'"></i></a>';
                } else {
                    if (is_array($id)) {
                        $ID = end($id);
                    } else {
                        $ID = $id;
                    }
                    $btn .= '<form id="delete-form-'.$ID.'" action="'.route($key.'.'.$k, $id).'" method="POST" class=" create-form my-0 py-0">
                           <input type="hidden" name="_token" value="'.csrf_token().'" />
                                                            <input type="hidden" name="_method" value="DELETE" class="my-0 py-0" />
                                                            <button type="submit" class="btn btn-link text-dark"><i class="'.$value.'"></i></button>

                        </form>';
                }

            }
        }

        return '<div class="d-flex align-items-center">'.$btn.'</div>';
    }

    /**
     * @return string
     */
    public function check($status)
    {

        switch ($status) {
            case 0:
                return 'Inactive';
                break;
            case 1:
                return 'Active';
                break;
            case 2:
                return 'Pending';
                break;
            default:
                return 'Undefined';
        }
    }

    public function currency_convert($amount, $from, $to)
    {
        if (($amount == 0) || ($from == $to)) {
            return $amount;
        }
        $currency = ($from !== 'USD') ? $from : $to;
        $ct = CurrencyConvertor::where('currency', $currency)
            ->where('startdate', '<=', Carbon::now()->format('Y-m-d'))
            ->where('enddate', '>', Carbon::now()->format('Y-m-d'))
            ->first();
        if (! is_null($ct)) {
            if ($from != 'USD' && $to == 'USD') {
                $amount = ($amount / $ct->amount) * $ct->dollar_amount;
            } elseif ($from == 'USD') {
                $amount = ($amount / $ct->dollar_amount) * $ct->amount;
            } else {
                $amount = Currency::convert()
                    ->from($from)
                    ->to($to)
                    ->amount($amount)
                    ->get();
                Log::error('Google'.$amount);
            }
        } else {

            $amount = Currency::convert()
                ->from($from)
                ->to($to)
                ->amount($amount)
                ->get();
        }

        return $amount;
    }

    /**
     * @return int|void
     */
    public function search($search)
    {

        $search = strtolower($search);
        if (strtolower($search) == 'active') {
            return 1;
        }
        if (strtolower($search) == 'inactive') {
            return 0;
        }
        if (strtolower($search) == 'pending') {
            return 2;
        }
        if (strtolower($search) == 'paid') {
            return 1;
        }
        if (strtolower($search) == 'not paid') {
            return 0;
        }

    }

    public function payment_check($status)
    {

        switch ($status) {
            case 1:
                return 'Paid';
            case 0:
                return 'Not Paid';

            default:
                return 'Undefined';
        }

    }

    public function verify_email($email)
    {

        $result = Http::withBasicAuth('api', (string) config('custom.MAIL.MAILGUN_API_KEY'))
            ->connectTimeout(3)
            ->timeout(8)
            ->retry([100, 300], throw: false)
            ->get('https://api.mailgun.net/v4/address/validate', [
                'address' => trim($email),
            ]);
        if ($result->successful()) {
            $data = $result->object();
            // dd($data);
            if ($data->result === 'deliverable' && ! (bool) $data->is_disposable_address) {
                User::where('email', trim($email))
                    ->update(['is_verified' => 2, 'verification_count' => 1]);
            } else {
                User::where('email', trim($email))
                    ->update(['is_verified' => 0, 'verification_count' => 1]);
            }
        }
    }

    public function identifer($model, $column, $size = 8)
    {

        $identifier = strtoupper(Str::random($size));
        mark:
        $model = '\\App\\Models\\'.$model;
        $check = $model::where($column, $identifier)
            ->first();
        if (! is_null($check)) {
            $identifier = $identifier.($check->id + 1);
            goto mark;
        }

        return $identifier;
    }

    public function discount($check)
    {

        return ($check == 0) ? 'Percentage' : 'fixed';
    }

    public function discount_r($check)
    {

        return (strtolower($check) == 'percentage') ? 0 : 1;
    }

    public function discount_calc($coupon, $amount, $region, $pod, $user, $rate)
    {
        $detail = new stdClass;
        $disc = 0;
        $check = null;

        $discount = Coupon::where('code', $coupon)
            ->where('start_date', '<=', Carbon::now())
                          // ->where('expiry_date', '>=', Carbon::now())
            ->where('region_id', $region->id)
            ->where(function ($query) use ($rate) {
                $query->where('rate_type', $rate)
                    ->orWhereHas('rateTypes', function ($query) use ($rate) {
                        $query->where('rate_type_id', $rate);
                    });
            })
                          // ->where('rate_type', $rate)
            ->whereJsonContains('products', $pod)
            ->first();

        if ($discount && @$discount->expires) {
            if (date_create($discount->expiry_date) <= Carbon::now()) {
                $discount = null;
            }
        }

        if (! is_null($discount)) {
            if ($discount->multi_use == 0) {
                $check = Transaction::where('coupon_code', $coupon)
                    ->where('user_id', $user)
                    ->where('status', 1)
                    ->first();
            }

            if (is_null($check)) {
                if ($discount->type == 0) {
                    // Percentage discount
                    $disc = floor(($discount->discount / 100) * $amount);

                } else {
                    // Fixed amount discount
                    $disc = min($discount->discount, $amount);
                }
            }
        }

        $cost = max($amount - $disc, 0); // Ensure cost is not negative

        $detail->discount = $disc;
        $detail->amount = $cost;
        $detail->total_amount = $amount;

        return $detail;
    }

    public function s3_path($path)
    {
        return 'https://'.config('filesystems.disks.s3.bucket').'.s3.'.config('filesystems.disks.s3.region').'.amazonaws.com/'.$path;
    }
}
