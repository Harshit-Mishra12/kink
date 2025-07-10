<?php

namespace App\Http\Controllers\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TermsAndConditions;
use App\Models\User;
use App\Models\UserBankDetails;
use App\Models\UserDocuments;
use Exception;
use Illuminate\Http\Request;



class UserController extends Controller
{
    public function fetchUsers(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'filters' => 'nullable|array', // Filters must be an array
            'filters.*.key' => 'required|string|in:language,gender,orientation,country,age_category', // Allowed keys
            'filters.*.value' => 'required|string', // Corresponding values
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            // Default pagination parameters
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $filters = $request->input('filters', []);

            // Query to fetch users with role 'ANONYMOUSUSER' and responses
            $query = User::where('role', 'ANONYMOUSUSER')
                ->whereHas('responses') // Ensure the user has at least one response
                ->when(!empty($filters), function ($q) use ($filters) {
                    foreach ($filters as $filter) {
                        $filterKey = $filter['key'];
                        $filterValue = $filter['value'];

                        // Apply filter dynamically
                        $q->where($filterKey, $filterValue);
                    }
                })
                ->orderBy('created_at', 'desc'); // Order by latest users first

            // Paginate the results
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            // Return success response
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'Users fetched successfully!',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors and return failure response
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
