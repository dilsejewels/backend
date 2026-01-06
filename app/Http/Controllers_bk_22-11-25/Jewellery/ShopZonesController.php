<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopZone;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopZonesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ShopZone::with('country')->orderBy('zone_id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    return '<button class="btn btn-sm btn-primary editBtn" data-id="'.$row->zone_id.'">Edit</button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->zone_id.'">Delete</button>';
                })
                ->make(true);
        }

        return view('admin.jewellery.ShopZones.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_country_id' => 'required',
            'zone_code' => 'required|max:100',
            'zone_name' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        ShopZone::create([
            'zone_country_id' => $request->zone_country_id,
            'zone_code' => $request->zone_code,
            'zone_name' => $request->zone_name,
            'added_by' => Auth::id()
        ]);

        return response()->json(['success' => 'Zone added successfully!']);
    }

    public function edit($id)
    {
        $zone = ShopZone::findOrFail($id);
        return response()->json($zone);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'zone_country_id' => 'required',
            'zone_code' => 'required|max:100',
            'zone_name' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        ShopZone::where('zone_id', $id)->update([
            'zone_country_id' => $request->zone_country_id,
            'zone_code' => $request->zone_code,
            'zone_name' => $request->zone_name,
            'updated_by' => Auth::id()
        ]);

        return response()->json(['success' => 'Zone updated successfully!']);
    }

    public function destroy($id)
    {
        ShopZone::where('zone_id', $id)->delete();
        return response()->json(['success' => 'Zone deleted successfully!']);
    }
}
