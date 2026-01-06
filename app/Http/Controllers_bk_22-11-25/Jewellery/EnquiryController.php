<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index()
    {
        $enquiries = Enquiry::latest()->get();
        return view('admin.Jewellery.enquiry.index', compact('enquiries'));
    }

    public function show($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        return view('admin.Jewellery.enquiry.show', compact('enquiry'));
    }

    public function destroy($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->delete();
        
        return redirect()->route('enquiries.index')
            ->with('success', 'Enquiry deleted successfully');
    }
}