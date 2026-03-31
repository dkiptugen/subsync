<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class IpWhitelist extends Model
        {
            use HasFactory;

            protected $fillable
                = [
                    'ip_address',
                    'organization_id',
                    'product_id',
                    'concurrent_connections',
                    'user_id',
                    'reason',
                    'status',
                    'startdate',
                    'enddate'
                ];
        }
