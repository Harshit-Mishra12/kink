<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{
    public function fetchUserReport(Request $request)
    {
        // Validate the incoming request to ensure user_id is provided
      // Validate the incoming request to ensure user_id is provided
    $request->validate([
        'user_id' => 'required|exists:users,id',  // Ensure the user_id exists in the users table
    ]);

    try {
        // Get the user_id from the request
        $user_id = $request->user_id;

        // Corrected SQL query to get the report data for all categories
        $query = "
            SELECT
                c.id AS category_id,
                c.name AS category_name,
                SUM(o.percentage) AS total_score,
                (SUM(o.percentage) / MAX(total_possible_score)) * 100 AS user_percentage_score
            FROM
                responses r
            JOIN
                options o ON r.option_id = o.id
            JOIN
                questions q ON r.question_id = q.id
            JOIN
                question_categories qc ON q.id = qc.question_id
            JOIN
                categories c ON qc.category_id = c.id
            LEFT JOIN
                (
                    SELECT
                        qc.category_id,
                        SUM(o.percentage) AS total_possible_score
                    FROM
                        question_categories qc
                    JOIN
                        questions q ON qc.question_id = q.id
                    JOIN
                        options o ON o.id IN (
                            SELECT option_id
                            FROM responses
                            WHERE question_id = q.id
                        )
                    GROUP BY
                        qc.category_id
                ) AS total_scores ON c.id = total_scores.category_id
            WHERE
                r.user_id = :user_id
            GROUP BY
                c.id, c.name
            ORDER BY
                category_name;
        ";

        // Execute the query with the provided user_id
        $results = DB::select(DB::raw($query), ['user_id' => $user_id]);

        // Return success response with the results
        return response()->json([
            'status_code' => 1,  // Success
            'message' => 'User report fetched successfully!',
            'data' => $results,  // Return the report data
        ], 200);  // HTTP status code 200 (OK)

    } catch (\Exception $e) {
        // Handle any errors and return failure response with status code 2
        return response()->json([
            'status_code' => 2,  // Failure
            'message' => 'Something went wrong. Please try again.',
            'error' => $e->getMessage(),
        ], 500);  // HTTP status code 500 (Internal Server Error)
    }
}

}
