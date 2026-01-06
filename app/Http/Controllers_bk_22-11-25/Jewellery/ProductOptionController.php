<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductOption;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ProductOptionController extends Controller
{
    public function index()
    {
        $productOptions = ProductOption::orderBy('options_id', 'DESC')->get();
        return view('admin.jewellery.ProductOption.index', compact('productOptions'));
    }

    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductOption::orderBy('options_id', 'DESC')->get();
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary editBtn" data-id="'.$row->options_id.'">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->options_id.'">Delete</button>
                    ';
                })
                ->editColumn('is_compulsory', function ($row) {
                    return $row->is_compulsory ? 'Yes' : 'No';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'options_name' => 'required|string|max:150',
            'options_type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ProductOption::create([
            'options_name' => $request->options_name,
            'options_type' => $request->options_type,
            'sort_order' => $request->sort_order,
            'is_compulsory' => $request->is_compulsory,
            'added_by' => auth()->id(),
            'date_added' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Product Option added successfully.']);
    }

    public function edit($id)
    {
        $data = ProductOption::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'options_name' => 'required|string|max:150',
            'options_type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $option = ProductOption::findOrFail($id);
        $option->update([
            'options_name' => $request->options_name,
            'options_type' => $request->options_type,
            'sort_order' => $request->sort_order,
            'is_compulsory' => $request->is_compulsory,
            'updated_by' => auth()->id(),
            'date_modified' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Product Option updated successfully.']);
    }

    public function destroy($id)
    {
        ProductOption::destroy($id);
        return response()->json(['message' => 'Product Option deleted successfully.']);
    }
}
