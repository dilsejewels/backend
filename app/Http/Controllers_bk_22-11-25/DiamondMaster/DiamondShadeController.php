<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Models\DiamondShade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiamondShadeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $shades = DiamondShade::all();
            return response()->json($shades);
        }
        return view('admin.DiamondMaster.Shade.index');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'ds_name' => 'nullable|string',
            'ds_short_name' => 'nullable|string',
            'ds_alise' => 'nullable|string',
            'ds_remark' => 'nullable|string',
            'ds_display_in_front' => 'nullable|integer',
            'ds_sort_order' => 'nullable|integer',

        ]);
        $data['date_added'] = now();

        DiamondShade::create($data);

        return redirect()->route('shades.index')
            ->with('success', 'Record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $shade = DiamondShade::findOrFail($id);

        $data = $request->validate([
            'ds_name' => 'nullable|string',
            'ds_short_name' => 'nullable|string',
            'ds_alise' => 'nullable|string',
            'ds_remark' => 'nullable|string',
            'ds_display_in_front' => 'nullable|integer',
            'ds_sort_order' => 'nullable|integer',
        ]);
          $data['date_modify'] = now();
        $shade->update($data);

        return redirect()->route('shades.index')
            ->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $shade = DiamondShade::findOrFail($id);
        $shade->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->route('shades.index')
            ->with('success', 'Record deleted successfully.');
    }

    public function show(DiamondShade $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.diamond_master.shade.edit', compact('shade'));
    }
}
