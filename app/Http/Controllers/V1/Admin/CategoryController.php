<?php

namespace App\Http\Controllers\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;



class CategoryController extends Controller
{
    public function saveCategory(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'category_id' => 'nullable|exists:categories,id', // Optional if creating a new category
            'language' => 'required|string|in:en,de,fr', // Language of the translation
            'name' => 'required|string|max:255|unique:category_translations,name,NULL,id,language,' . $request->language,
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'content' => 'required|string', // HTML content
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif', // Optional if updating only translations
            'selected_question' => 'nullable|array', // Optional if creating a new category
            'selected_question.*' => 'exists:questions,id', // Validate question IDs if provided
        ]);

        try {
            // Check if we're creating a new category or updating an existing one
            $category = $request->category_id
                ? Category::findOrFail($request->category_id) // Fetch the existing category
                : new Category();

            // If it's a new category, save the image and general category data
            if (!$request->category_id) {
                $imagePath = Helper::saveImageToServer($request->file('image'), 'uploads/categories/');
                $category->image = $imagePath; // Save the image path in the database
                $category->save();

                // Attach questions to the new category, if provided
                if ($request->selected_question) {
                    $category->questions()->attach($request->selected_question);
                }
            }

            // Check if a translation for the given language already exists
            $existingTranslation = $category->translations()->where('language', $request->language)->first();
            if ($existingTranslation) {
                return response()->json([
                    'status_code' => 2, // Failure
                    'message' => 'A translation for this language already exists for the given category.',
                ]); // HTTP status code 400 (Bad Request)
            }

            // Add the new translation for the specified language
            $translation = $category->translations()->create([
                'language' => $request->language,
                'name' => $request->name,
                'title' => $request->title,
                'short_description' => $request->short_description,
                'content' => $request->content,
            ]);

            // Return success response
            return response()->json([
                'status_code' => 1, // Success
                'message' => $request->category_id
                    ? 'Translation added successfully!'
                    : 'Category and translation created successfully!',
                'category' => $category->load('translations', 'questions'), // Eager load relationships
            ]); // HTTP status code 201 (Created)

        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500); // HTTP status code 500 (Internal Server Error)
        }
    }

    public function updateCategory(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'category_id' => 'required|exists:categories,id', // Ensure the category ID exists
            'language' => 'required|string|in:en,de,fr', // Language of the translation
            'name' => 'required|string|max:255|unique:category_translations,name,' . $request->translation_id . ',id,language,' . $request->language,
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'content' => 'required|string', // HTML content
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif', // Validate image file (optional during update)
            'selected_question' => 'nullable|array', // Array of selected question IDs
            'selected_question.*' => 'exists:questions,id', // Validate that the IDs exist in the questions table
        ]);

        try {
            // Find the category by ID
            $category = Category::findOrFail($request->category_id);

            // Check if the translation exists for the given language
            $translation = $category->translations()->where('language', $request->language)->first();
            if (!$translation) {
                return response()->json([
                    'status_code' => 2, // Failure
                    'message' => 'Translation for the specified language does not exist.',
                ]); // HTTP status code 404 (Not Found)
            }

            // Check if a new image is uploaded
            if ($request->hasFile('image')) {
                $imagePath = Helper::saveImageToServer($request->file('image'), 'uploads/categories/');
                $category->update(['image' => $imagePath]); // Update the category image
            }

            // Update the translation with the provided data
            $translation->update([
                'name' => $request->name,
                'title' => $request->title,
                'short_description' => $request->short_description,
                'content' => $request->content,
            ]);

            // If selected questions are provided, sync the relationships
            if ($request->has('selected_question')) {
                $category->questions()->sync($request->selected_question); // Detach old and attach new questions
            }

            // Eager load relationships and return the response
            $category = $category->load('translations', 'questions');

            return response()->json([
                'status_code' => 1, // Success
                'message' => 'Category and translation updated successfully!',
                'category' => $category, // Include updated category and translations
            ]); // HTTP status code 200 (OK)

        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500); // HTTP status code 500 (Internal Server Error)
        }
    }
    public function fetchAllCategories(Request $request)
    {
        // Validate the incoming request to ensure the language parameter is provided and valid
        $request->validate([
            'language' => 'required|string|in:en,de,fr', // Ensure the language is provided and valid
        ]);

        try {
            // Fetch all categories with translations for the specified language and their associated questions
            $categories = Category::with([
                'translations' => function ($query) use ($request) {
                    // Only include translations for the requested language
                    $query->where('language', $request->language);
                },
                'questions' // Include associated questions
            ])->get();

            // Filter out categories that don't have a translation for the requested language
            $categories = $categories->filter(function ($category) {
                return $category->translations->isNotEmpty(); // Keep only categories that have a translation for the requested language
            });

            // If there are no categories with the requested language, return an empty array
            if ($categories->isEmpty()) {
                return response()->json([
                    'status_code' => 2, // Failure
                    'message' => 'No categories found for the requested language.',
                ]); // HTTP status code 404 (Not Found)
            }

            // Map the categories to return only relevant translation for each category
            $categories = $categories->map(function ($category) use ($request) {
                $translation = $category->translations->first(); // Get the translation for the requested language

                return [
                    'id' => $category->id,
                    'image' => $category->image,
                    'name' => $translation ? $translation->name : null,
                    'title' => $translation ? $translation->title : null,
                    'short_description' => $translation ? $translation->short_description : null,
                    'content' => $translation ? $translation->content : null,
                    'questions' => $category->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question' => $question->question_text, // Include only relevant fields for questions
                        ];
                    }),
                ];
            });

            // Return success response with all categories that have translations in the requested language
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'Categories fetched successfully!',
                'categories' => $categories, // Return filtered and formatted categories
            ]); // HTTP status code 200 (OK)

        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500); // HTTP status code 500 (Internal Server Error)
        }
    }

    public function fetchCategoryById(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
            'language' => 'required|string|in:en,de,fr', // Ensure the language is provided and valid
        ]);

        try {
            // Get the category ID and language from the request
            $category_id = $request->category_id;
            $language = $request->language;

            // Find the category by category_id and eager load its associated questions and translations for the requested language
            $category = Category::with([
                'translations' => function ($query) use ($language) {
                    $query->where('language', $language); // Only fetch the translation for the requested language
                },
                'questions' // Include associated questions
            ])->findOrFail($category_id);

            // Check if a translation exists for the requested language
            if ($category->translations->isEmpty()) {
                return response()->json([
                    'status_code' => 2,  // Failure
                    'message' => 'Category does not have a translation for the requested language.',
                ]);  // HTTP status code 404 (Not Found)
            }

            // Get the translation for the requested language
            $translation = $category->translations->first();

            // Format the category response with the translation data
            $categoryData = [
                'id' => $category->id,
                'image' => $category->image,
                'name' => $translation->name,
                'title' => $translation->title,
                'short_description' => $translation->short_description,
                'content' => $translation->content,
                'questions' => $category->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question' => $question->question_text,
                    ];
                }),
            ];

            // Return success response with the category and its associated questions
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Category fetched successfully!',
                'category' => $categoryData,  // Return the formatted category with its translations and questions
            ]);  // HTTP status code 200 (OK)

        } catch (\Exception $e) {
            // Handle any errors and return failure response with status code 2
            return response()->json([
                'status_code' => 2,  // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);  // HTTP status code 500 (Internal Server Error)
        }
    }
    public function deleteCategory(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
        ]);

        try {
            // Get the category ID from the request
            $category_id = $request->category_id;

            // Find the category by category_id
            $category = Category::findOrFail($category_id);

            // Detach any related questions from the category
            $category->questions()->detach();

            // Delete the category and its translations
            $category->translations()->delete();  // Ensure that related translations are also deleted

            // Delete the category
            $category->delete();

            // Return success response
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Category deleted successfully!',
            ]);  // HTTP status code 200 (OK)

        } catch (\Exception $e) {
            // Handle any errors and return failure response with status code 2
            return response()->json([
                'status_code' => 2,  // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);  // HTTP status code 500 (Internal Server Error)
        }
    }






    // public function saveCategory(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:categories,name',
    //         'title' => 'required|string|max:255',
    //         'image' => 'required|image|mimes:jpg,jpeg,png,gif',  // Validate image file
    //         'short_description' => 'required|string|max:500',
    //         'content' => 'required|string', // HTML content
    //         'selected_question' => 'required|array', // Array of selected question IDs
    //         'selected_question.*' => 'exists:questions,id', // Validate that the IDs exist in the questions table
    //     ]);

    //     try {
    //         // Save the image and get the file path
    //         $imagePath = Helper::saveImageToServer($request->file('image'), 'uploads/categories/');

    //         // Create the category
    //         $category = Category::create([
    //             'name' => $request->name,
    //             'title' => $request->title,
    //             'image' => $imagePath,  // Save the image path in the database
    //             'short_description' => $request->short_description,
    //             'content' => $request->content,
    //         ]);

    //         // Attach the selected questions to the newly created category
    //         $category->questions()->attach($request->selected_question);

    //         // Eager load the associated questions and return the response with them inside the 'category' object
    //         $category = $category->load('questions');  // Eager load the questions relationship

    //         // Return success response with status code 1
    //         return response()->json([
    //             'status_code' => 1,  // Success
    //             'message' => 'Category created and linked to selected questions successfully!',
    //             'category' => $category,  // The category object now includes the associated questions
    //         ]);  // HTTP status code 201 (Created)

    //     } catch (\Exception $e) {
    //         // Handle any errors and return failure response with status code 2
    //         return response()->json([
    //             'status_code' => 2,  // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);  // HTTP status code 500 (Internal Server Error)
    //     }
    // }

    // public function updateCategory(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
    //         'name' => 'required|string|max:255|unique:categories,name,' . $request->category_id, // Ensure the name is unique except for the current category
    //         'title' => 'required|string|max:255',
    //         'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',  // Validate image file, but it's optional during update
    //         'short_description' => 'required|string|max:500',
    //         'content' => 'required|string', // HTML content
    //         'selected_question' => 'nullable|array', // Array of selected question IDs (can be null)
    //         'selected_question.*' => 'exists:questions,id', // Validate that the IDs exist in the questions table
    //     ]);

    //     try {
    //         // Get the category ID from the request
    //         $category_id = $request->category_id;

    //         // Find the category by category_id
    //         $category = Category::findOrFail($category_id);

    //         // Check if an image was uploaded, if so, save it and update the image path
    //         if ($request->hasFile('image')) {
    //             $imagePath = Helper::saveImageToServer($request->file('image'), 'uploads/categories/');
    //             $category->image = $imagePath;  // Update the image path
    //         }

    //         // Update the category with the provided data
    //         $category->update([
    //             'name' => $request->name,
    //             'title' => $request->title,
    //             'short_description' => $request->short_description,
    //             'content' => $request->content,
    //         ]);

    //         // If new selected questions are provided, update the relationships
    //         if ($request->has('selected_question')) {
    //             // Detach old questions and attach the new ones
    //             $category->questions()->sync($request->selected_question);  // This will remove old links and add new ones
    //         }

    //         // Eager load the associated questions and return the updated category in the response
    //         $category = $category->load('questions');

    //         // Return success response with updated category and associated questions
    //         return response()->json([
    //             'status_code' => 1,  // Success
    //             'message' => 'Category updated successfully!',
    //             'category' => $category,  // Return the updated category with associated questions
    //         ]);  // HTTP status code 200 (OK)

    //     } catch (\Exception $e) {
    //         // Handle any errors and return failure response with status code 2
    //         return response()->json([
    //             'status_code' => 2,  // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);  // HTTP status code 500 (Internal Server Error)
    //     }
    // }
    // public function fetchAllCategories()
    // {
    //     try {
    //         // Fetch all categories and eager load their associated questions
    //         $categories = Category::with('questions')->get();

    //         // Return success response with categories and their associated questions
    //         return response()->json([
    //             'status_code' => 1,  // Success
    //             'message' => 'Categories fetched successfully!',
    //             'categories' => $categories,  // Return all categories with associated questions
    //         ]);  // HTTP status code 200 (OK)

    //     } catch (\Exception $e) {
    //         // Handle any errors and return failure response with status code 2
    //         return response()->json([
    //             'status_code' => 2,  // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);  // HTTP status code 500 (Internal Server Error)
    //     }
    // }

    // public function fetchCategoryById(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
    //     ]);

    //     try {
    //         // Get the category ID from the request
    //         $category_id = $request->category_id;

    //         // Find the category by category_id and eager load its associated questions
    //         $category = Category::with('questions')->findOrFail($category_id);

    //         // Return success response with the category and its associated questions
    //         return response()->json([
    //             'status_code' => 1,  // Success
    //             'message' => 'Category fetched successfully!',
    //             'category' => $category,  // Return the category with its associated questions
    //         ], 200);  // HTTP status code 200 (OK)

    //     } catch (\Exception $e) {
    //         // Handle any errors and return failure response with status code 2
    //         return response()->json([
    //             'status_code' => 2,  // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);  // HTTP status code 500 (Internal Server Error)
    //     }
    // }


    // public function deleteCategory(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
    //     ]);

    //     try {
    //         // Get the category ID from the request
    //         $category_id = $request->category_id;

    //         // Find the category by category_id
    //         $category = Category::findOrFail($category_id);

    //         // Detach any related questions from the category
    //         $category->questions()->detach();

    //         // Delete the category
    //         $category->delete();

    //         // Return success response
    //         return response()->json([
    //             'status_code' => 1,  // Success
    //             'message' => 'Category deleted successfully!',
    //         ], 200);  // HTTP status code 200 (OK)

    //     } catch (\Exception $e) {
    //         // Handle any errors and return failure response with status code 2
    //         return response()->json([
    //             'status_code' => 2,  // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);  // HTTP status code 500 (Internal Server Error)
    //     }
    // }
}
