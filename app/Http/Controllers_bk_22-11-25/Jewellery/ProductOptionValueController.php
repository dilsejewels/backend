<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductOptionValue;
use App\Models\ProductOption;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ProductOptionValueController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductOptionValue::with('option')->orderBy('value_id', 'DESC');
            return DataTables::of($data)
                ->addColumn('option_name', function($row) {
                    return $row->option->options_name ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary editBtn" data-id="' . $row->value_id . '">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="' . $row->value_id . '">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $options = ProductOption::all();
        return view('admin.jewellery.ProductOptionValue.index', compact('options'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'options_id' => 'required|integer|exists:products_options,options_id',
            'value_name' => 'required|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductOptionValue::create([
            'options_id' => $request->options_id,
            'value_name' => $request->value_name,
            'sort_order' => $request->sort_order,
            'date_added' => Carbon::now(),
            'added_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Option value added successfully.']);
    }

    public function edit($id)
    {
        return response()->json(ProductOptionValue::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'options_id' => 'required|integer|exists:products_options,options_id',
            'value_name' => 'required|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductOptionValue::findOrFail($id)->update([
            'options_id' => $request->options_id,
            'value_name' => $request->value_name,
            'sort_order' => $request->sort_order,
            'date_modified' => Carbon::now(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Option value updated successfully.']);
    }

    public function destroy($id)
    {
        ProductOptionValue::destroy($id);
        return response()->json(['message' => 'Option value deleted successfully.']);
    }
}
