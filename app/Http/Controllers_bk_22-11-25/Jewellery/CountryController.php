<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $countries = Country::orderBy('country_id', 'desc')->get();

            return datatables()->of($countries)
                ->addColumn('is_active', fn($row) => $row->is_active ? 'Yes' : 'No')
                ->addColumn('actions', function ($row) {
                    return '<button class="btn btn-sm btn-primary editBtn" data-id="' . $row->country_id . '">Edit</button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="' . $row->country_id . '">Delete</button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.jewellery.Country.index');
    }

    public function store(Request $request)
    {
        $messages = [
            'country_name.required' => 'Country name is required.',
            'country_name.unique' => 'This country already exists.',
            'country_name.max' => 'Country name may not be greater than 100 characters.',
            'iso_code_2.max' => 'ISO Code 2 may not exceed 2 characters.',
            'iso_code_3.max' => 'ISO Code 3 may not exceed 3 characters.',
            'phone_code.max' => 'Phone code may not exceed 10 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'country_name' => 'required|string|max:100|unique:countries,country_name,' . $request->country_id . ',country_id',
            'iso_code_2' => 'nullable|string|max:2',
            'iso_code_3' => 'nullable|string|max:3',
            'phone_code' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['country_name', 'iso_code_2', 'iso_code_3', 'phone_code']);
        $data['is_active'] = $request->has('is_active') ? (bool)$request->is_active : false;

        if ($request->country_id) {
            $country = Country::find($request->country_id);
            $country->update($data);
        } else {
            $country = Country::create($data);
        }

        return response()->json(['success' => true, 'message' => 'Data saved successfully.']);
    }

    public function edit($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['error' => 'Data not found.'], 404);
        }
        return response()->json($country);
    }

    public function destroy($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['error' => 'Data not found.'], 404);
        }
        $country->delete();
        return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
    }
}
