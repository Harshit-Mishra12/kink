<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Models\Imprint;
use Illuminate\Http\Request;


class ImprintController extends Controller
{

     // Create or update Imprint
     public function createOrUpdateImprint(Request $request)
     {

         $validated = $request->validate([
             'language' => 'required|in:en,de', // Only allow 'en' (English) or 'de' (German)
             'content' => 'required|string', // HTML or string content
         ]);

         $imprint = Imprint::where('language', $validated['language'])->first();


         if ($imprint) {
             $imprint->update([
                 'content' => $validated['content'],
             ]);
             $message = 'Imprint has been updated successfully.';
         } else {
             $imprint = Imprint::create([
                 'language' => $validated['language'],
                 'content' => $validated['content'],
             ]);
             $message = 'Imprint has been created successfully.';
         }

         return response()->json([
             'status_code' => 1,
             'message' => $message,
             'data' => $imprint,
         ]);
     }

     // Fetch Imprint
     public function fetchImprint(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|in:en,de', // Only allow 'en' (English) or 'de' (German)
        ]);

        $imprint = Imprint::where('language', $validated['language'])->first();

        if (!$imprint) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Imprint information not found for the specified language.',
                'data' => [],
            ]);
        }

        return response()->json([
            'status_code' => 1,
            'data' => $imprint,
            'message' => 'Imprint fetched successfully.',
        ]);
    }
}
