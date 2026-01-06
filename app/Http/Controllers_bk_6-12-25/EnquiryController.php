<?php

namespace App\Http\Controllers;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'comments' => 'nullable|string',
            'product' => 'nullable|integer',
        ]);

        $enquiry = Enquiry::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry submitted successfully!',
            'data' => $enquiry,
        ], 201);
    }
}
