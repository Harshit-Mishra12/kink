<?php

namespace App\Http\Controllers\V1;

use App\Models\MetaTag;
use App\Models\MetaKeyword;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\MetaKeywordTranslation;
use App\Models\MetaTagTranslation;
use App\Models\Page;
use App\Models\PageMetaTag;

class MetaTagController extends Controller
{
    public function saveOrUpdateMetaTag(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:page,category', // Must be 'page' or 'category'
            'type_id' => 'required|string', // Example: 'about_us' for page, or '1' for category
            'language' => 'required|string|max:5', // Language code (e.g., 'en', 'de')
            'title' => 'required|string|max:255', // Title for the meta tag
            'description' => 'required|string', // Description for the meta tag
            'meta_keywords' => 'required|array', // Keywords for the meta tag
        ]);

        // Find or create the page (type + type_id)
        $page = Page::firstOrCreate([
            'type' => $validated['type'],
            'type_id' => $validated['type_id'],
        ]);

        // Save or update the meta tag for the specific language
        $metaTag = PageMetaTag::updateOrCreate(
            [
                'page_id' => $page->id,
                'language' => $validated['language'], // Language-specific entry
            ],
            [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'meta_keywords' => $validated['meta_keywords'],
            ]
        );


        return response()->json([
            'status_code' => 1, // Success
            'message' => 'Meta tag saved successfully!',
            'data' => $metaTag,
        ]);
    }
    public function fetchMetaTag(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:page,category', // Must be 'page' or 'category'
            'type_id' => 'required|string', // Example: 'about_us' for page, or '1' for category
            'language' => 'required|string|max:5', // Example: 'en', 'de'
        ]);

        try {
            // Fetch the page using type and type_id
            $page = Page::where('type', $validated['type'])
                ->where('type_id', $validated['type_id'])
                ->first();

            // If page not found, return error
            if (!$page) {
                return response()->json([
                    'status_code' => 2, // Failure
                    'message' => 'Page not found',
                ]);
            }

            // Fetch the meta tag for the specified language
            $metaTag = $page->metaTags()->where('language', $validated['language'])->first();

            // If meta tag not found, return error
            if (!$metaTag) {
                return response()->json([
                    'status_code' => 2, // Failure
                    'message' => 'Meta tag not found for the specified language',
                ]);
            }

            // Return the meta tag
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'Meta tag fetched successfully',
                'data' => [
                    'type' => $page->type,
                    'type_id' => $page->type_id,
                    'language' => $metaTag->language,
                    'title' => $metaTag->title,
                    'description' => $metaTag->description,
                    'meta_keywords' => $metaTag->meta_keywords,
                ],
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Failed to fetch meta tag: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function fetchMetaTag(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => 'required|string|in:page,category', // Must be 'page' or 'category'
    //         'type_id' => 'required|string', // Example: 'about_us' for page, or '1' for category
    //         'language' => 'required|string|max:5', // Example: 'en', 'de'
    //     ]);

    //     // Fetch the page using type and type_id
    //     $page = Page::where('type', $validated['type'])
    //         ->where('type_id', $validated['type_id'])
    //         ->first();

    //     // If page not found, return error
    //     if (!$page) {
    //         return response()->json(['message' => 'Page not found'], 404);
    //     }

    //     // Fetch the meta tag for the specified language
    //     $metaTag = $page->metaTags()->where('language', $validated['language'])->first();

    //     // If meta tag not found, return error
    //     if (!$metaTag) {
    //         return response()->json(['message' => 'Meta tag not found for the specified language'], 404);
    //     }

    //     // Return the meta tag
    //     return response()->json([
    //         'type' => $page->type,
    //         'type_id' => $page->type_id,
    //         'language' => $metaTag->language,
    //         'title' => $metaTag->title,
    //         'description' => $metaTag->description,
    //         'meta_keywords' => $metaTag->meta_keywords,
    //     ]);
    // }

    // public function saveOrUpdateMetaTag(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => 'required|in:page,category', // 'page' or 'category'
    //         'type_id' => 'required|string', // Identifier for the page or category
    //         'language' => 'required|string|max:5', // Language code for the translation
    //         'title' => 'nullable|string|max:255',
    //         'description' => 'nullable|string',
    //         'meta_keywords' => 'array', // Meta keywords as an array
    //         'meta_keywords.*' => 'required|string|max:50', // Each keyword is a string
    //     ]);

    //     try {
    //         // Find or create the meta tag
    //         $metaTag = MetaTag::updateOrCreate(
    //             ['type' => $validated['type'], 'type_id' => $validated['type_id']]
    //         );

    //         // Process the translation for the meta tag
    //         MetaTagTranslation::updateOrCreate(
    //             [
    //                 'meta_tag_id' => $metaTag->id,
    //                 'language' => $validated['language'],
    //             ],
    //             [
    //                 'title' => $validated['title'],
    //                 'description' => $validated['description'],
    //             ]
    //         );

    //         // Process meta keywords
    //         if (!empty($validated['meta_keywords'])) {
    //             // Get all keywords currently associated with the meta tag
    //             $existingKeywords = $metaTag->keywords;

    //             // Delete translations of existing keywords ONLY for the specified language
    //             foreach ($existingKeywords as $keyword) {
    //                 MetaKeywordTranslation::where([
    //                     'meta_keyword_id' => $keyword->id,
    //                     'language' => $validated['language'],
    //                 ])->delete();
    //             }

    //             // Save new keywords and their translations for the specified language
    //             $keywordIds = [];
    //             foreach ($validated['meta_keywords'] as $keywordName) {
    //                 // Find or create the keyword
    //                 $keywordModel = MetaKeyword::firstOrCreate(['name' => $keywordName]);

    //                 // Handle the translation for the keyword
    //                 MetaKeywordTranslation::updateOrCreate(
    //                     [
    //                         'meta_keyword_id' => $keywordModel->id,
    //                         'language' => $validated['language'],
    //                     ],
    //                     ['name' => $keywordName]
    //                 );

    //                 $keywordIds[] = $keywordModel->id;
    //             }

    //             // Sync keywords with the meta tag (this ensures the relationship is updated)
    //             $metaTag->keywords()->syncWithoutDetaching($keywordIds);
    //         }

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'Meta tag saved/updated successfully!',
    //             'data' => $metaTag->load('translations', 'keywords.translations'),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Failed to save/update meta tag.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    // public function saveOrUpdateMetaTag(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => 'required|in:page,category', // 'page' or 'category'
    //         'type_id' => 'required|string', // Identifier for the page or category
    //         'language' => 'required|string|max:5', // Language code for the translation
    //         'title' => 'nullable|string|max:255',
    //         'description' => 'nullable|string',
    //         'meta_keywords' => 'array', // Meta keywords as an array
    //         'meta_keywords.*' => 'required|string|max:50', // Each keyword is a string
    //     ]);

    //     try {
    //         // Find or create the meta tag
    //         $metaTag = MetaTag::updateOrCreate(
    //             ['type' => $validated['type'], 'type_id' => $validated['type_id']]
    //         );

    //         // Process the translation for the meta tag
    //         MetaTagTranslation::updateOrCreate(
    //             [
    //                 'meta_tag_id' => $metaTag->id,
    //                 'language' => $validated['language'],
    //             ],
    //             [
    //                 'title' => $validated['title'],
    //                 'description' => $validated['description'],
    //             ]
    //         );

    //         // Process meta keywords
    //         if (!empty($validated['meta_keywords'])) {
    //             $keywordIds = [];
    //             foreach ($validated['meta_keywords'] as $keywordName) {
    //                 $keywordModel = MetaKeyword::firstOrCreate(['name' => $keywordName]);

    //                 // Handle the translation for the keyword
    //                 MetaKeywordTranslation::updateOrCreate(
    //                     [
    //                         'meta_keyword_id' => $keywordModel->id,
    //                         'language' => $validated['language'],
    //                     ],
    //                     ['name' => $keywordName]
    //                 );

    //                 $keywordIds[] = $keywordModel->id;
    //             }

    //             // Sync keywords with the meta tag
    //             $metaTag->keywords()->sync($keywordIds);
    //         }

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'Meta tag saved/updated successfully!',
    //             'data' => $metaTag->load('translations', 'keywords.translations'),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Failed to save/update meta tag.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    // public function fetchMetaTag(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => 'required|in:page,category', // 'page' or 'category'
    //         'type_id' => 'required|string', // Identifier for the page or category
    //         'language' => 'required|string|max:5', // Language for fetching translations
    //     ]);

    //     try {
    //         // Fetch the meta tag
    //         $metaTag = MetaTag::where('type', $validated['type'])
    //             ->where('type_id', $validated['type_id'])
    //             ->with([
    //                 'translations' => function ($query) use ($validated) {
    //                     $query->where('language', $validated['language']);
    //                 },
    //                 'keywords.translations' => function ($query) use ($validated) {
    //                     $query->where('language', $validated['language']);
    //                 }
    //             ])
    //             ->first();

    //         if (!$metaTag) {
    //             return response()->json([
    //                 'status_code' => 2,
    //                 'message' => 'Meta tag not found.',
    //             ]);
    //         }

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'Meta tag fetched successfully!',
    //             'data' => $metaTag,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Failed to fetch meta tag.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }



    // public function saveOrUpdateMetaTag(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => 'required|in:page,category', // 'page' or 'category'
    //         'type_id' => 'required|string', // Identifier for the page or category
    //         'title' => 'nullable|string|max:255',
    //         'description' => 'nullable|string',
    //         'meta_keywords' => 'array', // Meta keywords as an array
    //         'meta_keywords.*' => 'string|max:50', // Each keyword is a string
    //     ]);

    //     try {
    //         // Find or create the meta tag
    //         $metaTag = MetaTag::updateOrCreate(
    //             ['type' => $validated['type'], 'type_id' => $validated['type_id']],
    //             ['title' => $validated['title'], 'description' => $validated['description']]
    //         );

    //         // Process meta keywords
    //         if (!empty($validated['meta_keywords'])) {
    //             $keywordIds = [];
    //             foreach ($validated['meta_keywords'] as $keyword) {
    //                 $keywordModel = MetaKeyword::firstOrCreate(['name' => $keyword]);
    //                 $keywordIds[] = $keywordModel->id;
    //             }

    //             // Sync keywords with the meta tag
    //             $metaTag->metaKeywords()->sync($keywordIds);
    //         }

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'Meta tag saved/updated successfully!',
    //             'data' => $metaTag->load('metaKeywords'),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Failed to save/update meta tag.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Fetch a meta tag with its associated meta keywords.
     */
    // public function fetchMetaTag(Request $request)
    // {

    //     $validated = $request->validate([
    //         'type' => 'required|in:page,category', // 'page' or 'category'
    //         'type_id' => 'required|string', // Identifier for the page or category
    //     ]);

    //     try {
    //         $metaTag = MetaTag::where('type', $validated['type'])
    //             ->where('type_id', $validated['type_id'])
    //             ->with('metaKeywords') // Load associated meta keywords
    //             ->first();

    //         if (!$metaTag) {
    //             return response()->json([
    //                 'status_code' => 2,
    //                 'message' => 'Meta tag not found.',
    //             ]);
    //         }

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'Meta tag fetched successfully!',
    //             'data' => $metaTag,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Failed to fetch meta tag.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
