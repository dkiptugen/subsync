<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
    use Spatie\Activitylog\Models\Concerns\LogsActivity;
    use Spatie\Activitylog\Support\LogOptions;


	class Site extends Model
		{

			protected $fillable
				= [
					'site_name',
					'site_url',
					'region_id'
				];
			use LogsActivity;

			public function getActivitylogOptions()
			: LogOptions
				{
					return LogOptions::defaults()->logOnly($this->fillable);
				}

			public function products()
				{
					return $this->hasMany(Product::class);
				}

			public function region()
				{

					return $this->belongsTo(Region::class);
				}

                public function otherProducts()
                {
                    return $this->belongsToMany(Product::class, 'product_sites', 'site_id', 'product_id');
                }
		}
