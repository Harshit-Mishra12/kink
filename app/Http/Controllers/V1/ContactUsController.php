<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;


class ContactUsController extends Controller
{

    public function savecontactus(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'language' => 'required|in:en,de', // Only allow 'en' (English) or 'de' (German)
            'content' => 'required|string', // Allow HTML or any other string content
        ]);

        // Try to find the existing contact entry by language
        $contactUs = ContactUs::where('language', $validated['language'])->first();

        // If entry exists, update it; otherwise, create a new one
        if ($contactUs) {
            $contactUs->update([
                'content' => $validated['content'],
            ]);
            $message = 'Contact Us content has been updated successfully.';
        } else {
            // Create a new entry if no entry exists for the given language
            $contactUs = ContactUs::create([
                'language' => $validated['language'],
                'content' => $validated['content'],
            ]);
            $message = 'Contact Us content has been saved successfully.';
        }

        // Return response
        return response()->json([
            'status_code' => 1,
            'message' => $message,
            'data' => $contactUs,
        ]);
    }

    public function fetchcontactus(Request $request)
    {
        // Validate that the language is either 'en' or 'de'
        $validated = $request->validate([
            'language' => 'required|in:en,de', // Only allow 'en' (English) or 'de' (German)
        ]);

        // Find the Contact Us entry for the requested language
        $contactUs = ContactUs::where('language', $validated['language'])->first();

        // Check if entry exists
        if ($contactUs) {
            return response()->json([
                'status_code' => 1,
                'message' => 'Contact Us information retrieved successfully.',
                'data' => $contactUs,
            ]);
        } else {
            return response()->json([
                'status_code' => 2,
                'message' => 'Contact Us information not found for the specified language.',
                'data' => [],
            ]);
        }
    }

}
