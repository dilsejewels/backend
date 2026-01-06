<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Http\Controllers\Controller;
use App\Models\DiamondLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiamondLabMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $labs = DiamondLab::all();
            return response()->json($labs);
        }
        return view('admin.DiamondMaster.Lab.index');
    }

    // Store new record
    public function store(Request $request)
    {
        $data = $request->validate([
            'dl_name' => 'required|string|max:250',
            'dl_display_in_front' => 'nullable|integer',
            'dl_sort_order' => 'nullable|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cert_url' => 'nullable|string|max:255',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('labs', $filename, 'public');
            $data['image'] = $filename;
        }
        
        $data['date_added'] = now();
        DiamondLab::create($data);

        return response()->json(['success' => true, 'message' => 'Record added successfully.']);
    }

    public function show($id)
    {
        $lab = DiamondLab::findOrFail($id);
        return response()->json($lab);
    }

    // Update record
    public function update(Request $request, $id)
    {
        $lab = DiamondLab::findOrFail($id);
        
        $data = $request->validate([
            'dl_name' => 'required|string|max:250',
            'dl_display_in_front' => 'nullable|integer',
            'dl_sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cert_url' => 'nullable|string|max:255',
            'existing_image' => 'nullable|string'
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($lab->image) {
                Storage::disk('public')->delete('labs/' . $lab->image);
            }
            
            // Store new image
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('labs', $filename, 'public');
            $data['image'] = $filename;
        } elseif ($request->input('existing_image') === '') {
            // Remove image if existing was cleared
            if ($lab->image) {
                Storage::disk('public')->delete('labs/' . $lab->image);
            }
            $data['image'] = null;
        } else {
            // Keep existing image
            $data['image'] = $lab->image;
        }
        
        $data['date_modify'] = now();
        $lab->update($data);

        return response()->json(['success' => true, 'message' => 'Record updated successfully.']);
    }

    // Delete record
    public function destroy($id)
    {
        $lab = DiamondLab::findOrFail($id);
        
        // Delete associated image
        if ($lab->image) {
            Storage::disk('public')->delete('labs/' . $lab->image);
        }
        
        $lab->delete();
        return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
    }
}