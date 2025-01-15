<?php

namespace App\Http\Controllers\V1;

use App\Models\MetaTag;
use App\Models\MetaKeyword;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;



class MetaTagController extends Controller
{

    public function saveOrUpdateMetaTag(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:page,category', // 'page' or 'category'
            'type_id' => 'required|string', // Identifier for the page or category
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'meta_keywords' => 'array', // Meta keywords as an array
            'meta_keywords.*' => 'string|max:50', // Each keyword is a string
        ]);

        try {
            // Find or create the meta tag
            $metaTag = MetaTag::updateOrCreate(
                ['type' => $validated['type'], 'type_id' => $validated['type_id']],
                ['title' => $validated['title'], 'description' => $validated['description']]
            );

            // Process meta keywords
            if (!empty($validated['meta_keywords'])) {
                $keywordIds = [];
                foreach ($validated['meta_keywords'] as $keyword) {
                    $keywordModel = MetaKeyword::firstOrCreate(['name' => $keyword]);
                    $keywordIds[] = $keywordModel->id;
                }

                // Sync keywords with the meta tag
                $metaTag->metaKeywords()->sync($keywordIds);
            }

            return response()->json([
                'status_code' => 1,
                'message' => 'Meta tag saved/updated successfully!',
                'data' => $metaTag->load('metaKeywords'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Failed to save/update meta tag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch a meta tag with its associated meta keywords.
     */
    public function fetchMetaTag(Request $request)
    {

        $validated = $request->validate([
            'type' => 'required|in:page,category', // 'page' or 'category'
            'type_id' => 'required|string', // Identifier for the page or category
        ]);

        try {
            $metaTag = MetaTag::where('type', $validated['type'])
                ->where('type_id', $validated['type_id'])
                ->with('metaKeywords') // Load associated meta keywords
                ->first();

            if (!$metaTag) {
                return response()->json([
                    'status_code' => 2,
                    'message' => 'Meta tag not found.',
                ]);
            }

            return response()->json([
                'status_code' => 1,
                'message' => 'Meta tag fetched successfully!',
                'data' => $metaTag,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Failed to fetch meta tag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
