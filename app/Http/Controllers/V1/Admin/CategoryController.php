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
            'name' => 'required|string|max:255|unique:categories,name',
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif',  // Validate image file
            'short_description' => 'required|string|max:500',
            'content' => 'required|string', // HTML content
            'selected_question' => 'required|array', // Array of selected question IDs
            'selected_question.*' => 'exists:questions,id', // Validate that the IDs exist in the questions table
        ]);

        try {
            // Save the image and get the file path
            $imagePath = Helper::saveImageToServer($request->file('image'), 'uploads/categories/');

            // Create the category
            $category = Category::create([
                'name' => $request->name,
                'title' => $request->title,
                'image' => $imagePath,  // Save the image path in the database
                'short_description' => $request->short_description,
                'content' => $request->content,
            ]);

            // Attach the selected questions to the newly created category
            $category->questions()->attach($request->selected_question);

            // Eager load the associated questions and return the response with them inside the 'category' object
            $category = $category->load('questions');  // Eager load the questions relationship

            // Return success response with status code 1
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Category created and linked to selected questions successfully!',
                'category' => $category,  // The category object now includes the associated questions
            ]);  // HTTP status code 201 (Created)

        } catch (\Exception $e) {
            // Handle any errors and return failure response with status code 2
            return response()->json([
                'status_code' => 2,  // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);  // HTTP status code 500 (Internal Server Error)
        }
    }

    public function updateCategory(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
            'name' => 'required|string|max:255|unique:categories,name,' . $request->category_id, // Ensure the name is unique except for the current category
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',  // Validate image file, but it's optional during update
            'short_description' => 'required|string|max:500',
            'content' => 'required|string', // HTML content
            'selected_question' => 'nullable|array', // Array of selected question IDs (can be null)
            'selected_question.*' => 'exists:questions,id', // Validate that the IDs exist in the questions table
        ]);

        try {
            // Get the category ID from the request
            $category_id = $request->category_id;

            // Find the category by category_id
            $category = Category::findOrFail($category_id);

            // Check if an image was uploaded, if so, save it and update the image path
            if ($request->hasFile('image')) {
                $imagePath = Helper::saveImageToServer($request->file('image'), 'uploads/categories/');
                $category->image = $imagePath;  // Update the image path
            }

            // Update the category with the provided data
            $category->update([
                'name' => $request->name,
                'title' => $request->title,
                'short_description' => $request->short_description,
                'content' => $request->content,
            ]);

            // If new selected questions are provided, update the relationships
            if ($request->has('selected_question')) {
                // Detach old questions and attach the new ones
                $category->questions()->sync($request->selected_question);  // This will remove old links and add new ones
            }

            // Eager load the associated questions and return the updated category in the response
            $category = $category->load('questions');

            // Return success response with updated category and associated questions
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Category updated successfully!',
                'category' => $category,  // Return the updated category with associated questions
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
    public function fetchAllCategories()
    {
        try {
            // Fetch all categories and eager load their associated questions
            $categories = Category::with('questions')->get();

            // Return success response with categories and their associated questions
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Categories fetched successfully!',
                'categories' => $categories,  // Return all categories with associated questions
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

    public function fetchCategoryById(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'category_id' => 'required|exists:categories,id', // Ensure the category ID exists in the categories table
        ]);

        try {
            // Get the category ID from the request
            $category_id = $request->category_id;

            // Find the category by category_id and eager load its associated questions
            $category = Category::with('questions')->findOrFail($category_id);

            // Return success response with the category and its associated questions
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Category fetched successfully!',
                'category' => $category,  // Return the category with its associated questions
            ], 200);  // HTTP status code 200 (OK)

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

            // Delete the category
            $category->delete();

            // Return success response
            return response()->json([
                'status_code' => 1,  // Success
                'message' => 'Category deleted successfully!',
            ], 200);  // HTTP status code 200 (OK)

        } catch (\Exception $e) {
            // Handle any errors and return failure response with status code 2
            return response()->json([
                'status_code' => 2,  // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);  // HTTP status code 500 (Internal Server Error)
        }
    }
}
