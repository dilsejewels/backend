<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Show the appointment management page
     */
    public function index()
    {
        return view('admin.Jewellery.appointments.index');
    }

    /**
     * Fetch all appointments for DataTable
     */
    public function fetch()
    {
        try {
            $appointments = Appointment::orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching appointments'
            ], 500);
        }
    }

    /**
     * Get single appointment details
     */
    public function show($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
    }

    /**
     * Delete an appointment
     */
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Appointment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting appointment'
            ], 500);
        }
    }

    /**
     * Get appointment statistics for dashboard
     */
    public function getStats()
    {
        try {
            $total = Appointment::count();
            $virtual = Appointment::where('appointment_type', 'virtual')->count();
            $showroom = Appointment::where('appointment_type', 'showroom')->count();
            $today = Appointment::whereDate('appointment_date', Carbon::today())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'virtual' => $virtual,
                    'showroom' => $showroom,
                    'today' => $today
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
