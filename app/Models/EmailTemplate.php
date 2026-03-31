<?php
	
	namespace App\Models;
	

	use App\Casts\JsonIntCast;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	
	class EmailTemplate extends Model
		{
			use HasFactory;
			protected $fillable = ['name', 'slug', 'subject', 'body','products','type','status','user_id'];
			protected $casts = ['products'=>JsonIntCast::class];
			
			public function user ()
				{
					return $this->belongsTo (User::class);
			}
		}
