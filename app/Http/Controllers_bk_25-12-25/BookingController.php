<?php

namespace App\Http\Controllers;

use App\Models\{Appointment,Provider,Service};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class BookingController extends Controller
{
    //
    public function availableSlots(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'service_id'  => 'required|exists:services,id',
            'appointment_date' => 'required|date',
        ]);

        $provider = Provider::find($request->provider_id);
        $service  = Service::find($request->service_id);
        $appointment_date     = Carbon::parse($request->appointment_date);
        $dayName  = $appointment_date->format('D');

        if (!in_array($dayName, $provider->available_days ?? [])) {
            return response()->json(['slots' => []]); // not working that day
        }

        $start = Carbon::parse($provider->start_time);
        $end   = Carbon::parse($provider->end_time);
        $period = CarbonPeriod::create($start, $service->duration.' minutes', $end);

        $booked = Appointment::where('provider_id',$provider->id)
                    ->whereDate('appointment_date',$appointment_date)->pluck('appointment_time')->toArray();

        $available = [];
        foreach ($period as $slot) {
            $slotTime = $slot->format('H:i:s');
            if (!in_array($slotTime, $booked)) {
                $available[] = $slotTime;
            }
        }

        return response()->json(['slots'=>$available]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'nullable|exists:providers,id',
            'service_id' => 'nullable|exists:services,id',
            'name' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'appointment_type' => 'required|in:virtual,showroom',
            'location' => 'nullable|string|max:255',
        ]);

        $appointment = Appointment::create($validated);

        return response()->json([
            'message' => 'Appointment booked successfully. Await confirmation from our staff.',
            'appointment' => $appointment
        ]);
    }
    // Book appointment
    public function book(Request $request)
    {
        $validated = $request->validate([
            'provider_id'=>'required|exists:providers,id',
            'service_id'=>'required|exists:services,id',
            'name'=>'required|string|max:100',
            'contact_number'=>'required|string|max:20',
            'email'=>'nullable|email',
            'appointment_date'=>'required|date',
            'appointment_time'=>'required',
        ]);

        $exists = Appointment::where('provider_id',$request->provider_id)
            ->where('appointment_date',$request->appointment_date)
            ->where('appointment_time',$request->appointment_time)
            ->exists();

        if ($exists) {
            return response()->json(['error'=>'Slot already booked'],409);
        }

        $appointment = Appointment::create($validated);
        return response()->json(['message'=>'Appointment confirmed!','data'=>$appointment]);
    }

    public function services(){ return Service::all(); }
    public function providers(){ return Provider::all(); }
}
