<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

class QuestionsController extends Controller
{
    public function fetchQuestions(Request $request)
    {
        try {
            // Validate the input language (if provided)
            $request->validate([
                'language' => 'required|string|max:2',  // e.g., 'en', 'de'
            ]);

            // Fetch active questions along with their translations in the requested language
            $language = $request->language;

            $questions = Question::where('is_active', true)
                ->with(['translations' => function ($query) use ($language) {
                    // Filter the translations by the provided language
                    $query->where('language', $language);
                }])
                ->get();

            // Check if any questions are found
            if ($questions->isEmpty()) {
                return response()->json([
                    'status_code' => 2,
                    'data' => [],
                    'message' => 'No active questions found.',
                ]);
            }

            // Format questions with their translations
            $formattedQuestions = $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'is_active' => $question->is_active,
                    'question_text' => $question->translations->first()->text ?? null, // Get translated text

                ];
            });

            // Return the questions with translations in the desired response format
            return response()->json([
                'status_code' => 1,  // Success
                'data' => $formattedQuestions,
                'message' => 'Questions fetched successfully.',
            ]);
        } catch (\Exception $e) {
            // Handle any errors that occur during fetching questions
            return response()->json([
                'status_code' => 2,  // Error
                'data' => [],
                'message' => 'Error fetching questions: ' . $e->getMessage(),
            ]);
        }
    }


    // public function fetchQuestions()
    // {
    //     try {
    //         // Fetch active questions from the database
    //         $questions = Question::where('status', 'active')->get();

    //         // Check if any questions are found
    //         if ($questions->isEmpty()) {
    //             return response()->json([
    //                 'status_code' => 2,
    //                 'data' => [],
    //                 'message' => 'No active questions found.',
    //             ]);
    //         }

    //         // Return questions in the desired response format
    //         return response()->json([
    //             'status_code' => 1,  // Success
    //             'data' => $questions,
    //             'message' => 'Questions fetched successfully.',
    //         ]);

    //     } catch (\Exception $e) {
    //         // Handle any errors that occur during fetching questions
    //         return response()->json([
    //             'status_code' => 2,  // Error
    //             'data' => [],
    //             'message' => 'Error fetching questions: ' . $e->getMessage(),
    //         ]);
    //     }
    // }

}
