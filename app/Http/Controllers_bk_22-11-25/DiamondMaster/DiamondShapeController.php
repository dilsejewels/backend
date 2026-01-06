<?php

namespace App\Http\Controllers\DiamondMaster;

use App\Http\Controllers\Controller;
use App\Models\DiamondShape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiamondShapeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $shapes = DiamondShape::all();
            return response()->json([
                'data' => $shapes
            ]);
        }

        return view('admin.DiamondMaster.Shape.index');
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data = $this->handleImageUploads($request, $data);
        $data['date_added'] = now();
        
        $shape = DiamondShape::create($data);

        return response()->json([
            'success' => true, 
            'message' => 'Record added successfully.',
            'data' => $shape
        ]);
    }

    public function update(Request $request, $id)
    {
        $shape = DiamondShape::findOrFail($id);
        $data = $this->validateRequest($request);
        $data = $this->handleImageUploads($request, $data, $shape);
        $data['date_modify'] = now();
        
        $shape->update($data);

        return response()->json([
            'success' => true, 
            'message' => 'Record updated successfully.',
            'data' => $shape
        ]);
    }

    private function validateRequest(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'ALIAS' => 'nullable|string|max:255',
            'shortname' => 'nullable|string|max:15',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image4' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'svg_image' => 'nullable|string',
            'remark' => 'nullable|integer',
            'display_in_front' => 'required|integer|in:0,1',
            'display_in_stud' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'existing_image' => 'nullable|string',
            'existing_image2' => 'nullable|string',
            'existing_image3' => 'nullable|string',
            'existing_image4' => 'nullable|string',
        ]);
    }

    private function handleImageUploads(Request $request, array $data, $existingRecord = null)
    {
        $imageFields = ['image', 'image2', 'image3', 'image4'];
        
        foreach ($imageFields as $field) {
            // Handle image removal
            if ($request->input('existing_'.$field) === '') {
                if ($existingRecord && $existingRecord->$field) {
                    Storage::disk('public')->delete('shapes/'.$existingRecord->$field);
                }
                $data[$field] = null;
            }
            // Handle new image upload
            elseif ($request->hasFile($field)) {
                // Delete old image if exists
                if ($existingRecord && $existingRecord->$field) {
                    Storage::disk('public')->delete('shapes/'.$existingRecord->$field);
                }
                
                // Generate unique filename
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(20).'.'.$extension;
                
                // Store file in public disk
                $path = $file->storeAs('shapes', $filename, 'public');
                $data[$field] = $filename;
            }
            // Keep existing image if no change
            elseif ($existingRecord) {
                $data[$field] = $existingRecord->$field;
            }
        }
        
        return $data;
    }

    public function destroy($id)
    {
        $shape = DiamondShape::findOrFail($id);
        
        // Delete all associated images
        $imageFields = ['image', 'image2', 'image3', 'image4'];
        foreach ($imageFields as $field) {
            if ($shape->$field) {
                Storage::disk('public')->delete('shapes/'.$shape->$field);
            }
        }
        
        $shape->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    }

    public function show($id)
    {
        $shape = DiamondShape::findOrFail($id);
        return response()->json($shape);
    }
}