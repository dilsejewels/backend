<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductsColorMaster;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ProductColorMasterController extends Controller
{
    public function index()
    {
        return view('admin.Jewellery.ProductColorMaster.index');
    }

    public function fetch(Request $request)
    {
        $data = ProductsColorMaster::orderBy('id', 'desc');
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-primary editBtn" data-id="'.$row->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
        ]);

        ProductsColorMaster::create([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'alias' => $request->alias,
            'remark' => $request->remark,
            'display_in_front' => $request->display_in_front,
            'sort_order' => $request->sort_order,
            'date_added' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Product Color Added Successfully']);
    }

    public function edit($id)
    {
        $data = ProductsColorMaster::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
        ]);

        $data = ProductsColorMaster::findOrFail($id);
        $data->update([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'alias' => $request->alias,
            'remark' => $request->remark,
            'display_in_front' => $request->display_in_front,
            'sort_order' => $request->sort_order,
            'date_modify' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Product Color Updated Successfully']);
    }

    public function destroy($id)
    {
        ProductsColorMaster::findOrFail($id)->delete();
        return response()->json(['success' => 'Product Color Deleted Successfully']);
    }
}
