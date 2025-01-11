<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{





    public function fetchUserReport(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'user_id' => 'required|exists:users,id', // Ensure the user_id exists
    ]);

    try {
        $userId = $request->user_id;

        // Step 1: Fetch all user responses
        $responses = Response::where('user_id', $userId)->get();

        // Step 2: Initialize a report array to store scores for each category
        $report = [];

        // Step 3: Iterate through the user's responses
        foreach ($responses as $response) {
            $questionId = $response->question_id;
            $option = $response->option; // Get the related option (assumes 'option' relationship in Response model)
            $optionScore = $option->percentage; // Get the percentage score from the option

            // Step 4: Find all categories linked to the question
            $categories = Category::whereHas('questions', function ($query) use ($questionId) {
                $query->where('questions.id', $questionId);
            })->get();

            return $categories;
            // Step 5: Distribute the score to the linked categories
            foreach ($categories as $category) {
                if (!isset($report[$category->id])) {
                    $report[$category->id] = [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'total_score' => 0,
                        'total_possible_score' => 0,
                    ];
                }

                // Add the user's score to the category
                $report[$category->id]['total_score'] += $optionScore;

                // Step 6: Calculate the total possible score for this category
                $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
                    $query->whereHas('question.categories', function ($query) use ($category) {
                        $query->where('categories.id', $category->id);
                    });
                })->sum('percentage'); // Sum up all possible percentages for the category

                $report[$category->id]['total_possible_score'] = $totalPossibleScore;
            }
        }

        // Step 7: Calculate the percentage score for each category
        foreach ($report as &$categoryReport) {
            if ($categoryReport['total_possible_score'] > 0) {
                $categoryReport['percentage_score'] =
                    ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;
            } else {
                $categoryReport['percentage_score'] = 0; // Avoid division by zero
            }
        }

        // Return the report as a JSON response
        return response()->json([
            'status_code' => 1, // Success
            'message' => 'User report fetched successfully!',
            'data' => array_values($report), // Convert associative array to indexed array
        ], 200);

    } catch (\Exception $e) {
        // Handle errors
        return response()->json([
            'status_code' => 2, // Failure
            'message' => 'Something went wrong. Please try again.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
