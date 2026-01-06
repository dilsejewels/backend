<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Get all appointments (both virtual and showroom)
     */

     public function storeAppointment(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'appointment_type' => 'required|in:virtual,showroom',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'time_zone' => 'nullable|in:India,USA,Australia,United Kingdom,Germany,France,Italy,Spain,Netherlands,Switzerland,Sweden,Norway,Denmark,Belgium,Austria,Ireland,Finland,Poland,Portugal,Greece',
                'today_time' => 'nullable|string',
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'guest_email' => 'nullable|email',
                'contact_number' => 'nullable|string|max:20',
                'category' => 'required|in:Engagement Rings,Wedding Bands,Gifting Jewelry,Studs,Necklace,Anniversary/Eternity,Bracelets,Haute,Other',
                'other_category' => 'nullable|string|max:255',
                'additional_information' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Add current timestamp for today_time if not provided
            $todayTime = $request->today_time ?: Carbon::now()->format('Y-m-d H:i:s');

            // Create new appointment
            $appointment = Appointment::create([
                'appointment_type' => $request->appointment_type,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'time_zone' => $request->time_zone,
                'today_time' => $todayTime,
                'name' => $request->name,
                'email' => $request->email,
                'guest_email' => $request->guest_email,
                'contact_number' => $request->contact_number,
                'category' => $request->category,
                'other_category' => $request->other_category,
                'additional_information' => $request->additional_information
            ]);

            return response()->json([
                'success' => true,
                'data' => $appointment,
                'message' => 'Appointment booked successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAppointments(Request $request)
    {
        try {
            $appointments = Appointment::orderBy('appointment_date', 'desc')
                                     ->orderBy('appointment_time', 'desc')
                                     ->get();

            return response()->json([
                'success' => true,
                'data' => $appointments,
                'message' => 'Appointments fetched successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only virtual appointments
     */
    public function getVirtualAppointments(Request $request)
    {
        try {
            $virtualAppointments = Appointment::where('appointment_type', 'virtual')
                                            ->orderBy('appointment_date', 'desc')
                                            ->orderBy('appointment_time', 'desc')
                                            ->get();

            return response()->json([
                'success' => true,
                'data' => $virtualAppointments,
                'message' => 'Virtual appointments fetched successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching virtual appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only showroom appointments
     */
    public function getShowroomAppointments(Request $request)
    {
        try {
            $showroomAppointments = Appointment::where('appointment_type', 'showroom')
                                             ->orderBy('appointment_date', 'desc')
                                             ->orderBy('appointment_time', 'desc')
                                             ->get();

            return response()->json([
                'success' => true,
                'data' => $showroomAppointments,
                'message' => 'Showroom appointments fetched successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching showroom appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appointments statistics
     */
    public function getAppointmentStats()
    {
        try {
            $totalAppointments = Appointment::count();
            $virtualCount = Appointment::where('appointment_type', 'virtual')->count();
            $showroomCount = Appointment::where('appointment_type', 'showroom')->count();
            
            $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalAppointments,
                    'virtual' => $virtualCount,
                    'showroom' => $showroomCount,
                    'today' => $todayAppointments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics'
            ], 500);
        }
    }
}