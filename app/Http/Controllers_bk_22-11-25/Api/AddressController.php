<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function getAddress($user_id)
    {
        $address = Address::where('user_id', $user_id)->first();

        if (!$address) {
            return response()->json(null, 204); // No content
        }

        return response()->json($address);
    }

    // AddressController.php

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'address' => 'required|array',
            'phone_number' => 'required|string',
            'is_get_offer' => 'nullable|boolean',
        ]);

        $address = \App\Models\Address::updateOrCreate(
            ['user_id' => $validated['user_id']],
            $validated
        );

        return response()->json($address, 200);
    }


}