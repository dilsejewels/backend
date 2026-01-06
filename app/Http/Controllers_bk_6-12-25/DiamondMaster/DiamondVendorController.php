<?php
namespace App\Http\Controllers\DiamondMaster;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\DiamondVendor;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DiamondVendorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $vendors = DiamondVendor::all();
            return response()->json($vendors);
        }
        return view('admin.DiamondMaster.Vendor.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules());
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $validated = $validator->validated();
        
        // Set default values for required fields
        $defaultValues = [
            'vendor_status' => 0,
            'auto_status' => 0,
            'price_markup_type' => 1,
            'price_markup_value' => 0,
            'fancy_price_markup_value' => 0,
            'extra_markup' => 0,
            'extra_markup_value' => 0,
            'fancy_extra_markup' => 0,
            'fancy_extra_markup_value' => 0,
            'external_image' => 0,
            'external_video' => 0,
            'external_certificate' => 0,
            'if_display_vendor_stock_no' => 0,
            'show_price' => 0,
            'duplicate_feed' => 0,
            'display_invtry_before_login' => 0,
            'deleted' => 0,
            'rank' => 0,
            'buying' => 0,
            'buy_email' => 0,
            'price_grid' => 0,
            'display_certificate' => 0,
            'diamond_size_from' => 0,
            'diamond_size_to' => 0,
            'keep_price_same_ab' => 0,
            'cc_fee' => 0,
        ];
        
        $validated = array_merge($defaultValues, $validated);
        $validated['date_addded'] = Carbon::now();
        $validated['added_by'] = auth()->id();

        DiamondVendor::create($validated);
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        if (request()->ajax()) {
            $vendor = DiamondVendor::findOrFail($id);
            return response()->json($vendor);
        }
    }

    public function update(Request $request, $id)
    {
        $vendor = DiamondVendor::findOrFail($id);
        
        // Check if this is only status update
        if ($request->has('vendor_status') && count($request->all()) <= 3) {
            // This is status update only - use minimal validation
            $validator = Validator::make($request->all(), [
                'vendor_status' => 'required|integer|in:0,1'
            ]);
        } else {
            // This is full form update - use full validation
            $validator = Validator::make($request->all(), $this->validationRules());
        }
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $validated = $validator->validated();
        
        // For full update, remove these fields
        if (!($request->has('vendor_status') && count($request->all()) <= 3)) {
            unset($validated['date_addded']);
            unset($validated['added_by']);
            $validated['date_updated'] = now();
            $validated['update_by'] = auth()->id();
        }

        $vendor->update($validated);
        
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        DiamondVendor::destroy($id);
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        }
        return response()->json(['success' => true]);
    }

    private function validationRules()
    {
        return [
            'vendor_company_name' => 'required|string|max:150',
            'vendor_name' => 'required|string|max:255',
            'diamond_prefix' => 'required|string|max:150',
            'vendor_email' => 'required|email|max:100',
            'vendor_phone' => 'required|string|max:20',
            'vendor_cell' => 'nullable|string|max:25',
            'how_hear_about_us' => 'nullable|string|max:150',
            'other_manufacturer_value' => 'nullable|string|max:250',
            'vendor_status' => 'required|integer|in:0,1',
            'auto_status' => 'required|integer|in:0,1',
            'price_markup_type' => 'required|integer|in:1,2',
            'price_markup_value' => 'required|numeric|min:0',
            'fancy_price_markup_value' => 'required|numeric|min:0',
            'extra_markup' => 'required|integer|in:0,1',
            'extra_markup_value' => 'required|numeric|min:0',
            'fancy_extra_markup' => 'required|integer|in:0,1',
            'fancy_extra_markup_value' => 'required|numeric|min:0',
            'delivery_days' => 'required|string|max:20',
            'additional_shipping_day' => 'nullable|string|max:20',
            'additional_rap_discount' => 'nullable|string|max:50',
            'notification_email' => 'nullable|email',
            'data_path' => 'nullable|string',
            'media_path' => 'nullable|string|max:50',
            'external_image' => 'required|integer|in:0,1',
            'external_image_path' => 'nullable|string',
            'external_image_formula' => 'nullable|string',
            'external_video' => 'required|integer|in:0,1',
            'external_video_path' => 'nullable|string',
            'external_video_formula' => 'nullable|string',
            'external_certificate' => 'required|integer|in:0,1',
            'external_certificate_path' => 'nullable|string',
            'if_display_vendor_stock_no' => 'required|integer|in:0,1',
            'vm_diamond_type' => 'nullable|string|max:150',
            'show_price' => 'required|integer|in:0,1',
            'duplicate_feed' => 'required|integer|in:0,1',
            'display_invtry_before_login' => 'required|integer|in:0,1',
            'vendor_product_group' => 'nullable|string',
            'vendor_customer_group' => 'nullable|string',
            'deleted' => 'required|integer|in:0,1',
            'rank' => 'required|integer|min:0',
            'buying' => 'required|integer|in:0,1',
            'buy_email' => 'required|integer|in:0,1',
            'price_grid' => 'required|integer|in:0,1',
            'display_certificate' => 'required|integer|in:0,1',
            'change_status_days' => 'nullable|string|max:20',
            'diamond_size_from' => 'required|numeric|min:0',
            'diamond_size_to' => 'required|numeric|min:0',
            'allow_color' => 'nullable|string',
            'location' => 'nullable|string',
            'offer_days' => 'nullable|string|max:20',
            'keep_price_same_ab' => 'required|integer|in:0,1',
            'cc_fee' => 'required|integer|in:0,1',
            'date_addded' => 'nullable|date',
            'added_by' => 'nullable|integer',
            'date_updated' => 'nullable|date',
            'update_by' => 'nullable|integer',
        ];
    }
}