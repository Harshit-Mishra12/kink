<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Models\ContactUs;
use Illuminate\Http\Request;


class AboutUsController extends Controller
{

    public function createOrUpdateAboutUs(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'content' => 'required|string',
            'language' => 'required|in:en,de', // Only allow English or German
        ]);

        // Check if About Us entry already exists for the specified language
        $aboutUs = AboutUs::where('language', $validated['language'])->first();
        if ($aboutUs) {
            // Update existing record
            $aboutUs->update($validated);
            return response()->json([
                'status_code' => 1,
                'message' => 'About Us content updated successfully.',
                'data' => $aboutUs,
            ]);
        } else {
            // Create new record
            $aboutUs = AboutUs::create($validated);
            return response()->json([
                'status_code' => 1,
                'message' => 'About Us content created successfully.',
                'data' => $aboutUs,
            ]);
        }
    }
    public function fetchAboutUs(Request $request)
    {
        // Validate the language input
        $validated = $request->validate([
            'language' => 'required|in:en,de',
        ]);

        // Fetch the About Us content for the requested language
        $aboutUs = AboutUs::where('language', $validated['language'])->first();

        if ($aboutUs) {
            return response()->json([
                'status_code' => 1,
                'message' => 'About Us content retrieved successfully.',
                'data' => $aboutUs,
            ]);
        } else {
            return response()->json([
                'status_code' => 2,
                'message' => 'About Us content not found for the specified language.',
                'data' => [],
            ]);
        }
    }
}
