<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs; 
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'phone'    => 'required|string|max:20',
            'topic'    => 'nullable|string|max:255',
            'question' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = ContactUs::create($validator->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Your question has been received. We will get in touch with you shortly.',
            'data'    => $contact
        ], 200);
    }
}