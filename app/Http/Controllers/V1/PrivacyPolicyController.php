<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;


class PrivacyPolicyController extends Controller
{

    public function createOrUpdatePrivacyPolicy(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|in:en,de', // Only allow 'en' (English) or 'de' (German)
            'content' => 'required|string', // HTML or string content
        ]);

        $privacyPolicy = PrivacyPolicy::where('language', $validated['language'])->first();

        if ($privacyPolicy) {
            $privacyPolicy->update([
                'content' => $validated['content'],
            ]);
            $message = 'Privacy Policy has been updated successfully.';
        } else {
            $privacyPolicy = PrivacyPolicy::create([
                'language' => $validated['language'],
                'content' => $validated['content'],
            ]);
            $message = 'Privacy Policy has been created successfully.';
        }

        return response()->json([
            'status_code' => 1,
            'message' => $message,
            'data' => $privacyPolicy,
        ]);
    }

    // Fetch Privacy Policy
    public function fetchPrivacyPolicy(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|in:en,de', // Only allow 'en' (English) or 'de' (German)
        ]);

        $privacyPolicy = PrivacyPolicy::where('language', $validated['language'])->first();

        if (!$privacyPolicy) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Privacy Policy information not found for the specified language.',
                'data' => [],
            ]);
        }

        return response()->json([
            'status_code' => 1,
            'data' => $privacyPolicy,
            'message' => 'Privacy Policy fetched successfully.',
        ]);
    }

}
