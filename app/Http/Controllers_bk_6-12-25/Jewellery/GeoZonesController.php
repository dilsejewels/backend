<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopZonesToGeoZone;
use App\Models\Country;
use App\Models\ShopZone;
use App\Models\GeoZone;
use Illuminate\Support\Facades\Auth;

class GeoZonesController extends Controller
{
    public function index()
    {
        $countries = Country::select('country_id', 'country_name')->get();
        $zones = ShopZone::select('zone_id', 'zone_name')->get();
        $geoZones = GeoZone::select('geo_zone_id', 'geo_zone_name')->get();

        return view('admin.jewellery.GeoZones.index', compact('countries', 'zones', 'geoZones'));
    }
 
    public function fetch()
    {
        $data = ShopZonesToGeoZone::with(['country', 'zone', 'geoZone'])->get();
        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,country_id',
            'zone_id' => 'required|exists:shop_zones,zone_id',
            'geo_zone_id' => 'required|exists:shop_zones_to_geo_zones,association_id',
        ]);

        ShopZonesToGeoZone::create([
            'country_id' => $request->country_id,
            'zone_id' => $request->zone_id,
            'geo_zone_id' => $request->geo_zone_id,
            'created_by' => Auth::id()
        ]);

        return response()->json(['success' => 'Record saved successfully.']);
    }

    public function show($id)
    {
        $record = ShopZonesToGeoZone::findOrFail($id);
        return response()->json($record);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,country_id',
            'zone_id' => 'required|exists:shop_zones,zone_id',
            'geo_zone_id' => 'required|exists:geo_zones,geo_zone_id',
        ]);

        $record = ShopZonesToGeoZone::findOrFail($id);
        $record->update([
            'country_id' => $request->country_id,
            'zone_id' => $request->zone_id,
            'geo_zone_id' => $request->geo_zone_id,
            'updated_by' => Auth::id()
        ]);

        return response()->json(['success' => 'Record updated successfully.']);
    }

    public function destroy($id)
    {
        ShopZonesToGeoZone::destroy($id);
        return response()->json(['success' => 'Record deleted successfully.']);
    }
}
