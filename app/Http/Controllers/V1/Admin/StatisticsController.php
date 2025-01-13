<?php

namespace App\Http\Controllers\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ReportDownload;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;



class StatisticsController extends Controller
{
    public function fetchStatistics(Request $request)
    {
        // Validate the inputs
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'filters' => 'nullable|array',
            'filters.*.key' => 'required|string',
            'filters.*.value' => 'required|string',
        ]);

        // Initialize the query for users
        $query = User::query();

        // Apply date range filter if provided
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Apply other filters if provided
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

        // Get total users after applying filters
        $totalUsers = $query->count();

        // Get total submissions (responses) by users
        $userIds = $query->pluck('id');
        $totalSubmissions = Response::whereIn('user_id', $userIds)->count();

        // Get total number of downloads
        $totalDownloads = ReportDownload::whereIn('user_id', $userIds)->count();

        // Return the aggregated statistics
        return response()->json([
            'total_users' => $totalUsers,
            'total_submissions' => $totalSubmissions,
            'total_downloads' => $totalDownloads,
        ]);
    }

}
