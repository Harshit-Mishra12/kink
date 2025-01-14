<?php

namespace App\Http\Controllers\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ReportDownload;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function fetchStatistics(Request $request)
    {
        // Validate the inputs
        $validated = $request->validate([
            'start_date' => 'required|date', // Start date is required
            'end_date' => 'required|date',   // End date is required
            'filters' => 'nullable|array',
            'filters.*.key' => 'required|string',
            'filters.*.value' => 'required|string',
        ]);

        // Extract date range
        $startDate = Carbon::parse($validated['start_date'])->startOfDay(); // Start from beginning of the day
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();       // Include the full day

        // Initialize the query for users with role 'ANONYMOUSUSER'
        $query = User::where('role', 'ANONYMOUSUSER')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Apply additional filters if provided
        if ($request->filters && is_array($request->filters)) {
            foreach ($request->filters as $filter) {
                $key = $filter['key'];
                $value = $filter['value'];

                switch ($key) {
                    case 'gender':
                        $query->where('gender', $value);
                        break;
                    case 'age_category':
                        $ageRange = explode('-', $value);
                        if (count($ageRange) == 2) {
                            $query->whereBetween('age', [(int)$ageRange[0], (int)$ageRange[1]]);
                        }
                        break;
                    case 'language':
                        $query->where('language', $value);
                        break;
                    case 'orientation':
                        $query->where('orientation', $value);
                        break;
                    // Add more cases as needed for other filters
                }
            }
        }

        // Clone the query for reuse
        $userQuery = clone $query;

        // Get total users after applying filters and date range
        $totalUsers = $query->count();

        // Get user IDs for further queries
        $userIds = $query->pluck('id');

        // Get total submissions (responses) grouped by user_id within the same date range
        $responseQuery = Response::whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id', DB::raw('COUNT(*) as total_submissions'))
            ->groupBy('user_id');
        $totalSubmissions = $responseQuery->get();

        // Get total downloads by users within the same date range
        $downloadQuery = ReportDownload::whereBetween('created_at', [$startDate, $endDate]);

        $totalDownloads = $downloadQuery->count();


        // Return the aggregated statistics
        return response()->json([
            'total_users' => $totalUsers,
            'total_submissions' => count($totalSubmissions), // Grouped by user_id
            'total_downloads' => $totalDownloads,
            'users' => $userQuery->get(),
        ]);
    }


    // public function fetchStatistics(Request $request)
    // {
    //     // Validate the inputs
    //     $validated = $request->validate([
    //         'start_date' => 'nullable|date',
    //         'end_date' => 'nullable|date',
    //         'filters' => 'nullable|array',
    //         'filters.*.key' => 'required|string',
    //         'filters.*.value' => 'required|string',
    //     ]);

    //     // Initialize the query for users
    //     $query = User::query();

    //     // Apply date range filter for user creation if provided
    //     if ($request->start_date && $request->end_date) {
    //         $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
    //     }

    //     // Apply other filters if provided
    //     if ($request->filters && is_array($request->filters)) {
    //         foreach ($request->filters as $filter) {
    //             $key = $filter['key'];
    //             $value = $filter['value'];

    //             switch ($key) {
    //                 case 'gender':
    //                     $query->where('gender', $value);
    //                     break;
    //                 case 'age_category':
    //                     $ageRange = explode('-', $value);
    //                     if (count($ageRange) == 2) {
    //                         $query->whereBetween('age', [(int)$ageRange[0], (int)$ageRange[1]]);
    //                     }
    //                     break;
    //                 case 'language':
    //                     $query->where('language', $value);
    //                     break;
    //                 case 'orientation':
    //                     $query->where('orientation', $value);
    //                     break;
    //                 // Add more cases as needed for other filters
    //             }
    //         }
    //     }

    //     $userQuery = clone $query;

    //     // Get total users after applying filters
    //     $totalUsers = $query->count();

    //     // Get total submissions (responses) by users within date range
    //     $userIds = $query->pluck('id');
    //     $responseQuery = Response::whereIn('user_id', $userIds);
    //     if ($request->start_date && $request->end_date) {
    //         $responseQuery->whereBetween('created_at', [$request->start_date, $request->end_date]);
    //     }
    //     $totalSubmissions = $responseQuery->count();

    //     // Get total number of downloads within date range
    //     $downloadQuery = ReportDownload::whereIn('user_id', $userIds);
    //     if ($request->start_date && $request->end_date) {
    //         $downloadQuery->whereBetween('created_at', [$request->start_date, $request->end_date]);
    //     }
    //     $totalDownloads = $downloadQuery->count();

    //     // Return the aggregated statistics
    //     return response()->json([
    //         'total_users' => $totalUsers,
    //         'total_submissions' => $totalSubmissions,
    //         'total_downloads' => $totalDownloads,
    //         'users' => $userQuery->get(),
    //     ]);
    // }


}
