<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class CurrencyConvertor extends Model
        {
            use HasFactory;
            protected $fillable = ["currency","dollar_amount","amount","region_id","startdate","enddate","status","user_id",""];
            public function user()
                {
                    return $this->belongsTo(User::class);
                }
            public function region()
                {
                    return $this->belongsTo(Region::class);
                }
        }
