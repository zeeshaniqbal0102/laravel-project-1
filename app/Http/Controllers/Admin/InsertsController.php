<?php

namespace App\Http\Controllers\Admin;

use AclHelper;
use App\DB\Company;
use App\DB\Inserts;
use App\DB\Occasion;
use App\DB\Product;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request as IRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Input;
use Lang;
use Redirect;
use Request;
use View;
use Validator;

class InsertsController extends BaseController
{
    private $scope = 'inserts';
    private $imagePath = '/inserts/';
    private $imageHighResPath = '/inserts/high_res/';

    public function index()
    {
        // lists all the inserts
        $insertsCount = Inserts::with(['product', 'company'])->count();
        return view("admin.inserts.index", [
            "insertsCount" => $insertsCount,
            "scope" => $this->scope,
        ]);
    }

    public function add(IRequest $request)
    {
        if ($request->isMethod('POST')) {
            $messages = [
                'name.unique' => 'Unable to save. Name already exists',
            ];
            $rules = [
                'name' => 'required|unique:inserts',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'high_res_image' => 'image|mimes:jpeg,png,jpg',
                'size' => 'required',
                'occasion' => 'required',
                'sort_key' => 'required',
                'inventory' => 'required|numeric',
                'internal_notes' => 'string',
            ];
            $validator = Validator::make(
                $request->all(),
                $rules,
                $messages
            );

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator);
            }
            $occasions = $request->occasion;
            $insert = Inserts::create([
                'company_id' => $request->company,
                'available_to_all' => ($request->available_to_all) ? $request->available_to_all : 0,
                'name' => $request->name,
                'size' => null,
                'default_products' => $request->default_products,
                // 'occasion' => $request->occasion,
                'image' => $this->uploadImage(),
                'image_high' => $this->uploadImage(null, 1),
                'price' => $request->price,
                'sort_key' => $request->sort_key,
                'status' => empty($request->status) ? 0 : 1,
                'inventory' => $request->inventory,
                'internal_notes' => $request->internal_notes
            ]);
            $insert->occasion()->attach($request->occasion);
            $insert->sizes()->attach($request->size);

            return Redirect::route('inserts.index')->with(
                'message',
                Lang::get('response.CUSTOM_MESSAGE_SUCCESS', ['message' => "Inserts successfully created."])
            );
        }
        $products = Product::orderBY('sort_key', 'ASC')->get();
        $occasions = Occasion::all();
        return view("admin.inserts.add", [
            'scope' => $this->scope,
            'products' => $products,
            'occasions' => $occasions,
        ]);
    }

    public function edit(IRequest $request, $id)
    {
        if (!$inserts = Inserts::withTrashed()->find($id)) {
            return Redirect::route($this->scope . '.index')->with(
                'message',
                Lang::get('response.INVALID_REQUEST')
            );
        }
        
        $this->existing_image = $inserts->image;
        $this->existing_image_high = $inserts->image_high;
        if ($request->isMethod('POST')) {
            $messages = [
                'name.unique' => 'Unable to save. Name already exists',
            ];
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('inserts')->ignore($inserts->id),
                ],
                'size' => 'required',
                'occasion' => 'required',
                'sort_key' => 'required',
                'inventory' => 'required|numeric',
                'internal_notes' => 'string',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator);
            }

            $inserts->name = $request->name;
            $inserts->company_id = $request->company;
            $inserts->available_to_all = $request->available_to_all;
            $inserts->name = $request->name;
            $inserts->size = null;
            $inserts->default_products = $request->default_products;
            // $inserts->occasion = $request->occasion;
            $inserts->image = Input::file('image') ? $this->uploadImage($this->existing_image) : $this->existing_image;
            $inserts->image_high = Input::file('high_res_image') ? $this->uploadImage($this->existing_image_high, 1) : $this->existing_image_high;
            $inserts->price = $request->price;
            $inserts->sort_key = $request->sort_key;
            $inserts->status = empty($request->status) ? 0 : 1;
            $inserts->inventory = $request->inventory;
            $inserts->internal_notes = $request->internal_notes;
            $inserts->save();
            
            // occasion
            $occasions = $request->occasion;
            $inserts->occasion()->sync($occasions);
            $inserts->sizes()->sync($request->size);

            return Redirect::back()->withInput(Input::all())->with(
                'message',
                Lang::get('response.CUSTOM_MESSAGE_SUCCESS', ['message' => "Inserts successfully updated."])
            );
        }
        $products = Product::orderBY('sort_key', 'ASC')->get();
        $occasions = Occasion::all();
        $company = null;
        if ($inserts->company_id) {
            $company = Company::find($inserts->company_id);
        }
        $sizes = $inserts->sizes->pluck('id')->toArray();
        $selectedOccasions = $inserts->occasion()->pluck('id')->toArray();
        return view("admin.inserts.edit", [
            "inserts" => $inserts,
            "scope" => $this->scope,
            "products" => $products,
            "occasions" => $occasions,
            'company' => $company,
            'sizes' => $sizes,
            'selectedOccasions' => $selectedOccasions
        ]);
    }

    public function delete(IRequest $request, $id)
    {
        if ($inserts = Inserts::find($id)) {
            $inserts->occasion()->detach();
            $inserts->delete();
            return Redirect::route($this->scope . '.index')->with(
                'message',
                Lang::get('response.CUSTOM_MESSAGE_SUCCESS', ['message' => "Inserts successfully deleted."])
            );
        }
        return Redirect::route($this->scope . '.index')->with(
            'message',
            Lang::get('response.INVALID_REQUEST')
        );
    }

    /**
     * Upload image and return image name
     * @return null|string
     */
    public function uploadImage($logo = null, $image_type = 0)
    {
        if ($image_type == 0 && !Input::file('image')) {
            return null;
        }
        if ($image_type == 1 && !Input::file('high_res_image')) {
            return null;
        }
        if ($logo && file_exists((public_path() . $this->imagePath . $logo)) && !@unlink(public_path() . $this->imagePath . $logo)) {
            return Redirect::back()->withInput(Input::all())->with(
                'message',
                Lang::get(
                    'response.CUSTOM_MESSAGE_WARNING',
                    ['message' => "Update Failed. Image couldnot be deleted."]
                )
            );
        }
        if (Input::file('image') && $image_type == 0) {
            $this->file = Input::file('image');
            $this->image = $this->file->getClientOriginalName();
            $this->final_image = str_random(40). '.' . $this->file->getClientOriginalExtension();
            $this->file->move(public_path() . $this->imagePath, $this->final_image);
        } else {
            $this->file = Input::file('high_res_image');
            $this->image = $this->file->getClientOriginalName();
            $this->final_image = $this->image;
            $this->file->move(public_path() . $this->imageHighResPath, $this->final_image);
        }
        return $this->final_image;
    }

    /**
     * Generates random numbers
     * @return string
     */
    public function __getRandomNumbers()
    {
        return rand(5555, 9876) . '_';
    }

    public function ajaxLoadInserts($inserts = null)
    {
        $inserts = preg_replace('/[^A-Za-z0-9\-\s]/', '', $_GET['q']);

        $companyTotResultsCount = Inserts::where('status', 1)->where(
            'name',
            'like',
            '%' . $inserts . '%'
        )->count();
        $companyResults = Inserts::select('id', 'name')->where('status', 1)->where(
            'name',
            'like',
            '%' . $inserts . '%'
        )->simplePaginate(50)->all();

        $data = array(
            "total_count" => $companyTotResultsCount,
            "incomplete_results" => false,
            "items" => $companyResults
        );
        return $data;
    }

    public function ajaxLoadAllInserts()
    {
        $inserts = Inserts::withTrashed()->select('inserts.*', 'company.magento_name', 'company.name as company_name')
            ->leftJoin('company', 'company.id', '=', 'inserts.company_id');
        $datatables = app('datatables')->of($inserts)
            ->editColumn('available_to_all', function ($data) {
                return ($data->available_to_all) ? 'Yes' : 'No';
            })
            ->editColumn('image', function ($data) {
                $path = ($data->image_high) ? asset('/inserts/high_res/' . $data->image_high) : asset('/inserts/default.jpg');
                return '<img src="'. $path .'" width="200" height="200"/>';
            })
            ->addColumn('action', function ($data) {
                $html = '<a href="'. route('inserts.edit', ['id' => $data->id]) .'"><button class="btn btn-sm btn-primary"> Edit </button></a>&nbsp;&nbsp;&nbsp';
                if ($data->deleted_at == null) {
                    $html .= '<a href="'.route('inserts.delete',
                            ['id' => $data->id]).'" onclick="return showWarning();"><button class="btn btn-sm btn-danger confirm" data-confirm="Are you sure you want to delete this box card?"> Delete </button></a>&nbsp;&nbsp;&nbsp';
                }
                $html .= '<a href="' .route('inserts.copy', ['id' => $data->id]) .'"><button class="btn btn-sm btn-sm btn-info"> Copy </button></a>';
                return $html;
            })
            ->addColumn('company', function ($data) {
                return $data->magento_name ? "$data->magento_name($data->company_name)" : '';
            })
            ->rawColumns(['action', 'image']);
            $datatables->filterColumn('company', function($query, $keyword) {
                $query->where('company.magento_name', 'like', "%{$keyword}%")
                    ->orwhere('company.name', 'like', "%{$keyword}%");
            });
        return $datatables->make(true);
    }

    public function populateHighResImage()
    {
        $inserts = Inserts::whereNull('image_high')
            ->orWhere('image_high', '')
            ->simplePaginate(500);
        foreach ($inserts as $insert) {
            $imageName = "{$insert->name}.jpg";
            if (Storage::disk('public')->exists('/inserts/high_res/' . $imageName)) {
                if (empty($insert->image_high)) {
                    $insert->image_high = $imageName;
                    $insert->save();
                }
            }
        }
        return $inserts;
    }

    public function copy($id)
    {
        $insert = Inserts::withTrashed()->find($id);
        if (!$insert) {
            return redirect()->route('giftcard-images.index')
                ->with(
                    'message', 
                    Lang::get(
                        'response.CUSTOM_MESSAGE_WARNING', 
                        ['message' => 'Sorry, Giftcard is not In the system']
                    )
                );
        }

        $newInsert = $insert->replicate()->fill([
            'name' => $insert->name . '-copy-' . generate_random_string(4),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $newInsert->save();
        $newInsert->occasion()->attach($insert->occasion()->get(['id']));
        $newInsert->sizes()->attach($insert->sizes()->get(['id']));

        if (!$newInsert) {
            return redirect()->route('giftcard-images.index')
                ->with(
                    'message', 
                    Lang::get(
                        'response.CUSTOM_MESSAGE_WARNING', 
                        ['message' => 'Sorry, Cannot Proceed...']
                    )
                );
        }

        return redirect()->route('inserts.edit', ['id' => $newInsert->id]);
    }
}
