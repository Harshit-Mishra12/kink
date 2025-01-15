<?php

namespace App\Http\Controllers\V1\User;


use App\Http\Controllers\Controller;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{

    public function saveResponses(Request $request)
    {
        // Validate the incoming request
         $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.option_id' => 'required|exists:options,id',
        ]);

        try {
            // Start a database transaction
            DB::beginTransaction();

            $userId = $validatedData['user_id'];
            $responses = $validatedData['responses'];

            foreach ($responses as $response) {
                Response::create([
                    'user_id' => $userId,
                    'question_id' => $response['question_id'],
                    'option_id' => $response['option_id'],
                ]);

            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status_code' => 1,
                'data' => [],
                'message' => 'Responses saved successfully.',
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            return response()->json([
                'status_code' => 2,
                'data' => [],
                'message' => 'Failed to save responses: ' . $e->getMessage(),
            ]);
        }
    }

}
