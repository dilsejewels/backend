<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    // 🔹 Get address for authenticated user
    public function getAddress()
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->first();

        if (!$address) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No address found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    // 🔹 Store or update address
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'address' => 'required|array',
            'phone_number' => 'required|string|max:20',
            'is_get_offer' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $addressData = $validator->validated();
        $addressData['user_id'] = $user->id;

        // Create or update address
        $address = Address::updateOrCreate(
            ['user_id' => $user->id],
            $addressData
        );

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully',
            'data' => $address
        ], 200);
    }

    // 🔹 Delete address
    public function destroy($id)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->where('id', $id)->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    // 🔹 Get all addresses for user (if multiple addresses needed)
    public function getUserAddresses()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }
}