<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Http\Controllers\Controller;
use App\Models\DiamondKeyToSymbols;
use Illuminate\Http\Request;

class DiamondKeyToSymbolsMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $records = DiamondKeyToSymbols::all();
            return response()->json($records);
        }
        return view('admin.DiamondMaster.KeyToSymbols.index');
    }

    public function create()
    {
        return view('admin.DiamondMaster.KeyToSymbols.create');
    }

    // Store new record
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'alias' => 'nullable|string',
            'short_name' => 'nullable|string',
            'sym_status' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);
        $data['date_added'] = now();
        DiamondKeyToSymbols::create($data);

        return redirect()->route('keytosymbols.index')
            ->with('success', 'Record added successfully.');
    }

    public function edit($id)
    {
        $record = DiamondKeyToSymbols::findOrFail($id);
        return view('admin.DiamondMaster.KeyToSymbols.index', compact('record'));
    }

    public function show(DiamondKeyToSymbols $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        $record = $id;
        return view('admin.DiamondMaster.KeyToSymbols.index', compact('record'));
    }

    // Update operation
    public function update(Request $request, $id)
    {
        $record = DiamondKeyToSymbols::findOrFail($id);

        $rules = [];

        // Only require 'name' if it's included in the request
        if ($request->has('name')) {
            $rules['name'] = 'required|string';
        }

        if ($request->has('alias')) {
            $rules['alias'] = 'nullable|string';
        }

        if ($request->has('short_name')) {
            $rules['short_name'] = 'nullable|string';
        }

        if ($request->has('sym_status')) {
            $rules['sym_status'] = 'nullable|integer';
        }

        if ($request->has('sort_order')) {
            $rules['sort_order'] = 'nullable|integer';
        }

        // Validate request based on the conditional rules
        $data = $request->validate($rules);

        $data['date_modify'] = now();

        $record->update($data);

        return redirect()->route('keytosymbols.index')
            ->with('success', 'Record updated successfully.');
    }

    // Delete operation
    public function destroy($id)
    {
        $record = DiamondKeyToSymbols::findOrFail($id);
        $record->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }


        return redirect()->route('keytosymbols.index')
            ->with('success', 'Record deleted successfully.');
    }
}
