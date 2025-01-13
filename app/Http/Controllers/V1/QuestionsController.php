<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Question;


use Illuminate\Support\Facades\Log;

class QuestionsController extends Controller
{

        public function fetchQuestions()
        {
            try {
                // Fetch active questions from the database
                $questions = Question::where('status', 'active')->get();

                // Check if any questions are found
                if ($questions->isEmpty()) {
                    return response()->json([
                        'status_code' => 2,
                        'data' => [],
                        'message' => 'No active questions found.',
                    ]);
                }

                // Return questions in the desired response format
                return response()->json([
                    'status_code' => 1,  // Success
                    'data' => $questions,
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

}
