<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
    {
        use HasFactory;
        protected $fillable = ['identifier','product_name','type','payment_methods','product_link','user_id','payment_notification_link','status','site_id','article_cost'];
        protected $casts = ['payment_methods' => JsonCast::class];
        use LogsActivity;
        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults();
            }
        public function rates(): HasMany
            {
                return $this->hasMany(Rate::class);
            }
        public function subscription(): HasMany
            {
                return $this->hasMany(Subscription::class);
            }
        public function user(): BelongsTo
            {
               return $this->belongsTo(User::class) ;
            }

        public function PaymentMethods() : Attribute
            {
                return Attribute::make(
                    get: fn ($value, $attributes) => PaymentMethod::whereIn('id',json_decode($value))->get()
                )->shouldCache();

            }

        public function bundledProducts() : Attribute
            {
                return Attribute::make(
                    get: fn ($value, $attributes) => Product::whereIn('id',json_decode($value))->get()
                )->shouldCache();

            }
        public function site(): BelongsTo
            {
                return $this->belongsTo(Site::class);
            }

            public function children(): BelongsToMany
            {
                return $this->belongsToMany(Product::class,'product_products','product_id','child_product_id');
            }

            public function parents(): BelongsToMany
            {
                return $this->belongsToMany(Product::class,'product_products','child_product_id','product_id');
            }

            public function sites(): BelongsToMany
            {
                return $this->belongsToMany(Site::class,'product_sites','product_id','site_id');
            }

        public function getDescriptionPointsAttribute()
        {
            $html = $this->attributes['description'];

            // Normalize line breaks
            $html = str_replace(['<br>', '<br/>', '<br />'], "\n", $html);

            // 1. Try to extract <li> items
            preg_match_all('/<li[^>]*>(.*?)<\/li>/i', $html, $liMatches);
            if (!empty($liMatches[1])) {
                return array_map(fn($item) => trim(strip_tags($item)), $liMatches[1]);
            }

            // 2. If no <li>, try <p> blocks
            preg_match_all('/<p[^>]*>(.*?)<\/p>/i', $html, $pMatches);
            if (!empty($pMatches[1])) {
                return array_map(fn($item) => trim(strip_tags($item)), $pMatches[1]);
            }

            // 3. If no <p>, split by line breaks or punctuation
            $text = strip_tags($html);
            $lines = preg_split('/\r\n|\r|\n|\.\s*/', $text);

            return array_values(array_filter(array_map('trim', $lines)));
        }

        public function counterpart(): HasOne
        {
            return $this->hasOne(Product::class,'id','counterpart_id');
        }
    }
