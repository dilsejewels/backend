<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        return view('admin.Jewellery.User.index');
    }

    /**
     * Fetch users for DataTable
     */
    public function fetchUsers()
    {
        $users = User::with('addresses')->get();
        
        return datatables()->of($users)
            ->addColumn('action', function($user) {
                return '<button class="btn btn-sm btn-info view-user" data-id="'.$user->id.'">
                    <i class="fa fa-eye"></i> View
                </button>';
            })
            ->addColumn('user_type', function($user) {
                return ucfirst($user->user_type);
            })
            ->addColumn('image_url', function($user) {
                if ($user->image_url) {
                    return '<img src="'.$user->image_url.'" alt="User Image" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">';
                }
                return '<div style="width: 50px; height: 50px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <i class="fa fa-user"></i>
                </div>';
            })
            ->rawColumns(['action', 'image_url'])
            ->make(true);
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        $user = User::with('addresses')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'user' => $user,
            'addresses' => $user->addresses
        ]);
    }
}