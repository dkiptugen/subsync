<?php

namespace App\Http\Controllers;

use App\Enums\EmailType;
use App\Http\Datatables\EmailTemplateDatatable;
use App\Http\Requests\StoreEmailTemplate;
use App\Http\Requests\UpdateEmailTemplate;
use App\Models\EmailTemplate;
use App\Models\Product;
use App\Traits\Meta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailTemplateController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('modules.email_template.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data['products'] = Product::get();
        $this->data['types'] = EmailType::cases();

        return view('modules.email_template.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmailTemplate $request)
    {
        try {
            $validatedata = $request->validated();
            if ($validatedata) {
                $template = new EmailTemplate;
                $template->name = $request->template_name;
                $template->subject = $request->subject;
                $template->email_type = $request->template_type;
                $template->products = $request->products;
                $template->user_id = $request->user()->id;
                $res = $template->save();
                if ($res) {
                    return self::success('Email Template', 'saved successfully',
                        route('email_template.edit',
                            ['email_template' => $template->id]));
                }
                Log::error('Store Email Template', [
                    'error' => 'Unable to save', 'user' => $request->user()->email,
                ]);

                return self::failed('Email Template', 'Unable to save',
                    route('email_template.index'));

            } else {
                Log::error('Store Email Template', [
                    'error' => $validatedata, 'user' => $request->user()->email,
                ]);

                return self::failed('Email Template', 'Error encountered contact the developer',
                    route('email_template.index'));
            }
        } catch (\Exception $e) {
            Log::error('Store Email Template', [
                'error' => $e->getMessage(), 'user' => $request->user()->email,
            ]);

            return self::failed('Email Template', 'Error encountered contact the developer',
                route('email_template.index'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $email_template)
    {
        $this->data['email_body'] = $email_template->body;
        $this->data['user'] = Auth::user();

        return view('modules.email_template.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailTemplate $email_template)
    {
        $this->data['products'] = Product::get();
        $this->data['types'] = EmailType::cases();
        $this->data['variables'] = config('email.'.EmailType::from($email_template->email_type)
            ->name);
        $this->data['template'] = $email_template;

        return view('modules.email_template.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmailTemplate $request, EmailTemplate $email_template)
    {
        try {
            $validatedata = $request->validated();
            if ($validatedata) {
                $template = $email_template;
                $template->name = $request->template_name;
                $template->subject = $request->subject;
                $template->user_id = $request->user()->id;
                $template->email_type = $request->template_type;
                $template->products = $request->products;
                $template->body = $request->email_body;
                $template->status = $request->status ?? 0;
                $res = $template->save();
                if ($res) {
                    return self::success('Email Template', 'updated successfully',
                        route('email_template.index'));
                }
                Log::error('Store Email Template', [
                    'error' => 'Unable to save', 'user' => $request->user()->email,
                ]);

                return self::failed('Email Template', 'Unable to save',
                    route('email_template.index'));

            } else {
                Log::error('Store Email Template', [
                    'error' => $validatedata, 'user' => $request->user()->email,
                ]);

                return self::failed('Email Template', 'Error encountered contact the developer',
                    route('email_template.index'));
            }
        } catch (\Exception $e) {
            Log::error('Store Email Template', [
                'error' => $e->getMessage(), 'user' => $request->user()->email,
            ]);

            return self::failed('Email Template', 'Error encountered contact the developer',
                route('email_template.index'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $email_template)
    {
        try {
            if ($email_template->delete()) {
                return self::success('Email Template', 'Deleted successfully',
                    route('email_template.index'));
            }
            Log::error('Delete Email Template', [
                'error' => 'Failed Deletion', 'user' => Auth::user()->email,
            ]);

            return self::failed('Email Template', 'Error encountered contact the developer',
                route('email_template.index'));
        } catch (\Exception $e) {
            Log::error('Delete Email Template', [
                'error' => $e->getMessage(), 'user' => Auth::user()->email,
            ]);

            return self::failed('Email Template', 'Error encountered contact the developer',
                route('email_template.index'));
        }

    }

    /**
     * Custom method added for datatable.
     *
     * @return JsonResponse
     */
    public function datatable(Request $request, EmailTemplateDatatable $datatable)
    {
        $datatable->columns = [0 => 'id', 1 => 'name', 4 => 'type', 5 => 'status'];

        return response()->json($datatable->data($request));
    }
}
