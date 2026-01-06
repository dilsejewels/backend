<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Models\ContactUsResponse;
use App\Mail\ContactResponseMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ContactUsController extends Controller
{
    /**
     * Show the contact us management page
     */
    public function index()
    {
        return view('admin.Jewellery.contact_us.index');
    }

    /**
     * Fetch all contact us entries for DataTable
     */
    public function fetch()
    {
        try {
            $contacts = ContactUs::with(['latestResponse', 'latestResponse.responder'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching contact us entries: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching contact us entries'
            ], 500);
        }
    }

    /**
     * Get single contact us entry details with responses
     */
    public function show($id)
    {
        try {
            $contact = ContactUs::with(['responses', 'responses.responder'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching contact details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Contact entry not found'
            ], 404);
        }
    }

    /**
     * Send response to contact query
     */
    public function respond(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'required|string|min:10|max:2000'
            ]);

            $contact = ContactUs::findOrFail($id);

            // Create response record
            $response = ContactUsResponse::create([
                'contact_us_id' => $contact->id,
                'responded_by' => Auth::id(),
                'message' => $request->message
            ]);

            // Get responder name
            $responderName = Auth::user()->name ?? 'Admin Team';

            // Send email to the contact person
            Mail::to($contact->email)
                ->send(new ContactResponseMail($contact, $request->message, $responderName));

            return response()->json([
                'success' => true,
                'message' => 'Response sent successfully and email notification delivered!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending response: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending response: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a contact us entry
     */
    public function destroy($id)
    {
        try {
            $contact = ContactUs::findOrFail($id);

            // Delete related responses first
            $contact->responses()->delete();
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact entry and related responses deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting contact: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting contact entry'
            ], 500);
        }
    }

    /**
     * Get contact us statistics
     */
    public function getStats()
    {
        try {
            $total = ContactUs::count();
            $today = ContactUs::whereDate('created_at', Carbon::today())->count();
            $thisWeek = ContactUs::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();
            $thisMonth = ContactUs::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
            $pendingResponses = ContactUs::doesntHave('responses')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'today' => $today,
                    'this_week' => $thisWeek,
                    'this_month' => $thisMonth,
                    'pending_responses' => $pendingResponses
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics'
            ], 500);
        }
    }
}
