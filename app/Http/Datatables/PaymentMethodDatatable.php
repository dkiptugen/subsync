<?php

namespace App\Http\Datatables;

use App\Models\PaymentMethod;
use Carbon\Carbon;


class PaymentMethodDatatable
    {


        public $columns = [];

    /**
     * Generate DataTable data
     */
        public function data($request)
            {
                $columns = $this->columns;

                $query = PaymentMethod::query()
                                      ->with('user');

                $totalData     = $query->count();
                $totalFiltered = $totalData;

                $limit = $request->input('length');
                $start = $request->input('start');

                $order = $columns[$request->input('order.0.column')];
                $dir   = $request->input('order.0.dir');

                if (!empty($request->input('search.value')))
                    {
                        $search = $request->input('search.value');

                        $query->where(function ($q) use ($search)
                            {
                                $q
                                    ->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('identifier', 'LIKE', "%{$search}%")
                                    ->orWhere('provider', 'LIKE', "%{$search}%")
                                    ->orWhereHas('user', function ($ql) use ($search)
                                        {

                                            return $ql
                                                ->where('name', 'LIKE', "%{$search}%")
                                                ->orWhere('email', 'LIKE', "%{$search}%");
                                        });
                            });


                        $totalFiltered = (clone $query)->count();
                    }

                $items = $query
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $data = [];

                if (!empty($items))
                    {
                        $pos = $start + 1;

                        foreach ($items as $item)
                            {
                                $btn = $this->button($item, $request);

                                $nestedData['pos']        = $pos;
                                $nestedData['provider']   = $item->provider;
                                $nestedData['identifier'] = $item->identifier;
                                $nestedData['status']     = $this->check($item->status);
                                $nestedData['notify']     = ($item->notifying == 1)
                                    ? '<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input" id="customSwitch' . $item->id . '"  checked disabled>
                                        <label class="custom-control-label" for="customSwitch' . $item->id . '"></label>
									</div>'
                                    : '<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input shortcode-notify" id="customSwitch' . $item->id . '" data-shortcode="' . $item->identifier . '">
                                        <label class="custom-control-label" for="customSwitch' . $item->id . '" data-shortcode="' . $item->identifier . '">Activate</label>
									</div>';

                                $nestedData['creator']      = $item->user->name;
                                $nestedData['date_created'] = Carbon::parse($item->created_at)->toIso8601String();
                                $nestedData['action']       = $btn;

                                $data[] = $nestedData;

                                $pos++;
                            }
                    }

                return [
                    'draw'            => (int)$request->input('draw'),
                    'recordsTotal'    => $totalData,
                    'recordsFiltered' => $totalFiltered,
                    'data'            => $data
                ];
            }

    /**
     * Action buttons
     */
        private function button($item, $request)
            {
                $button = '';

                if ($request->user()->can('edit_paymentmethod'))
                    {
                        $button .= '<a class="text text-dark" href="' . route('paymentmethod.edit', $item->id) . '" title="Edit PaymentMethod">
                <i class="fas fa-edit"></i> Edit
            </a>';
                    }

                if ($request->user()->can('destroy_paymentmethod'))
                    {
                        $button .= '<form id="delete-form-' . $item->id . '" action="' . route('paymentmethod.destroy', $item->id) . '" method="POST" class="d-inline">
                <input type="hidden" name="_token" value="' . csrf_token() . '" />
                <input type="hidden" name="_method" value="DELETE" />
                <button type="submit" class="btn btn-link text-dark">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>';
                    }

                return '<div class="d-flex align-items-center gap-2">' . $button . '</div>';
            }

        private function check($status)
            {
                return '<span class="badge bg-' . $status->color() . '">
                            ' . $status->label() . '
                        </span>';
            }
    }
