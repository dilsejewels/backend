<?php
namespace App\Http\Controllers\DiamondMaster;
use App\Http\Controllers\Controller;
use App\Models\DiamondClarityMaster;
use Illuminate\Http\Request;

class DiamondClarityMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $clarities = DiamondClarityMaster::all();
            return response()->json($clarities);
        }
        return view('admin.DiamondMaster.Clarity.index');
    }

    // Form submit hone par naya record store karta hai
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string',
            'ALIAS'            => 'nullable|string',
            'remark'           => 'nullable|string',
            'display_in_front' => 'nullable|integer',
            'sort_order'       => 'nullable|integer',
        ]);
        $data['date_added'] = now();
        DiamondClarityMaster::create($data);

        return redirect()->route('clarity.index')
                         ->with('success', 'Record added successfully.');
    }


    // Update operation
    public function update(Request $request, $id)
    {
        $clarity = DiamondClarityMaster::findOrFail($id);

        $data = $request->validate([
            'name'             => 'required|string',
            'ALIAS'            => 'nullable|string',
            'remark'           => 'nullable|string',
            'display_in_front' => 'nullable|integer',
            'sort_order'       => 'nullable|integer',
        ]);
        $data['date_modify'] = now();
        $clarity->update($data);

        return redirect()->route('clarity.index')
                         ->with('success', 'Record updated successfully.');
    }

    // Delete operation
    public function destroy($id)
    {
        $clarity = DiamondClarityMaster::findOrFail($id);
        $clarity->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }

        return redirect()->route('clarity.index')
                         ->with('success', 'Record deleted successfully.');
    }
    public function show(DiamondClarityMaster $id)
    {
        if (request()->ajax()) {
            return response()->json($id);
        }
        return view('admin.diamond_master.clarity.edit', compact('clarity'));
    }
}
