<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Models\ContactUs;
use App\Models\HomePage;
use Illuminate\Http\Request;


class HomePageController extends Controller
{

    public function createOrUpdateHomePage(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'heading' => 'required|string',
            'content' => 'required|string',
            'language' => 'required|in:en,de', // Only allow English or German
        ]);

        // Store heading and content as JSON in the 'content' column
        $storedContent = json_encode([
            'heading' => $validated['heading'],
            'content' => $validated['content'],
        ]);

        // Check if a record exists for the given language
        $homePage = HomePage::where('language', $validated['language'])->first();

        if ($homePage) {
            // Update existing record
            $homePage->update(['content' => $storedContent]);
            return response()->json([
                'status_code' => 1,
                'message' => 'Home Page content updated successfully.',
                'data' => $homePage,
            ]);
        } else {
            // Create new record
            $homePage = HomePage::create([
                'content' => $storedContent,
                'language' => $validated['language']
            ]);

            return response()->json([
                'status_code' => 1,
                'message' => 'Home Page content created successfully.',
                'data' => $homePage,
            ]);
        }
    }
    public function fetchHomePage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,de',
        ]);

        $homePage = HomePage::where('language', $request->language)->first();

        if (!$homePage) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Home Page content not found.',
            ]);
        }

        // Decode stored JSON content
        $decodedContent = json_decode($homePage->content, true);

        return response()->json([
            'status_code' => 1,
            'message' => 'Home Page content retrieved successfully.',
            'data' => [
                'id' => $homePage->id,
                'heading' => $decodedContent['heading'] ?? '',
                'content' => $decodedContent['content'] ?? '',
                'created_at' => $homePage->created_at,
                'updated_at' => $homePage->updated_at,
            ],
        ]);
    }
}
