<?php

namespace App\Http\Datatables;

use App\Enums\EmailType;
use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\Product;
use App\Models\Site;
use App\Traits\Helper;
use App\Models\EmailTemplate;
use App\Traits\Meta;

class EmailTemplateDatatable
{
    use Meta;

    public $columns = [];

    /**
     * @param $request
     *
     * @return array{draw: int, recordsTotal: mixed, recordsFiltered: mixed, data: array}
     */
    public function data($request)
    {
        $columns       = $this->columns;
        $totalData     = EmailTemplate::count();
        $totalFiltered = $totalData;
        $limit         = $request->input('length');
        $start         = $request->input('start');
        $order         = $columns[$request->input('order.0.column')];
        $dir           = $request->input('order.0.dir');

        if (empty($request->input('search.value')))
        {
            $posts = EmailTemplate::offset($start)
                                  ->limit($limit)
                                  ->orderBy($order, $dir)
                                  ->get();
        }
        else
        {
            $search = $request->input('search.value');
            $posts  = EmailTemplate::where('name', 'LIKE', "%{$search}%")
                 
                ->offset($start)->limit($limit)->orderBy($order, $dir)->get();

            $totalFiltered = EmailTemplate::where('name', 'LIKE', "%{$search}%")
                 
                ->count();
        }

        $data = [];
        if (!empty($posts))
        {
            $pos = $start + 1;
            foreach ($posts as $post)
            {
                $btn                  =  self::button_generate('email_template', $post->id, [], ['destroy']);
                $nestedData['pos']     = $pos;
                $nestedData['name']   = $post->name;
                $nestedData['products']  = $this->get_products ($post->products);
                $nestedData['status'] = PaymentStatus::from($post->status == 1)->name;
	            $nestedData['type'] = EmailType::from($post->email_type)->name;
				$nestedData['creator'] = $post->user->name;
                $nestedData['action'] = $btn;

                $data[] = $nestedData;
                $pos++;
            }
        }

        $json_data = [
            'draw'            => (int)$request->input('draw'),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data
        ];

        return $json_data;
    }

    /**
     * @param $post
     * @param $request
     *
     * @return string
     */

		
		private function get_products ($products)
			{
				$pr = Product::whereIn('id',$products)
							->get();
				$tag = '<div>';
				foreach ($pr as $product)
					{
						$tag .= '<span class="badge badge-pill badge-dark m-1">'.$product->product_name.'</span>';
					}
				$tag .= '</div>';
			
				return $tag;
			}
}