<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;


use Illuminate\Support\Facades\Log;

class OptionsController extends Controller
{

    public function fetchOptions()
    {
        // Fetch all options
        $options = Option::all();

        // Check if options are found
        if ($options->isEmpty()) {
            return response()->json([
                'status_code' => 2, // Error
                'data' => [],
                'message' => 'No options found.',
            ]);
        }

        // Return the options with a success status code
        return response()->json([
            'status_code' => 1, // Success
            'data' => $options,
            'message' => 'Options fetched successfully.',
        ]);
    }

}
