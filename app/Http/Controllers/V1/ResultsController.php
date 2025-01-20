<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\ReportDownload;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF as DomPDFPDF;

class ResultsController extends Controller
{

    public function fetchUserReport(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user_id exists
            'language' => 'required|string|max:10', // Language code to fetch translations
        ]);

        try {
            $userId = $request->user_id;
            $language = $request->language;

            // Step 1: Fetch all user responses
            $responses = Response::where('user_id', $userId)->get();

            // Step 2: Initialize a report array to store scores for each category
            $report = [];

            // Step 3: Iterate through the user's responses
            foreach ($responses as $response) {
                $questionId = $response->question_id;
                $option = $response->option; // Get the related option (assumes 'option' relationship in Response model)
                $optionScore = $option->percentage; // Get the percentage score from the option

                // Step 4: Find all categories linked to the question
                $categories = Category::whereHas('questions', function ($query) use ($questionId) {
                    $query->where('questions.id', $questionId);
                })->get();

                // Step 5: Distribute the score to the linked categories
                foreach ($categories as $category) {
                    // Fetch the translated name for the category based on the language
                    $translation = $category->translations()->where('language', $language)->first();

                    if (!$translation) {
                        // Skip if no translation exists for the specified language
                        continue;
                    }

                    if (!isset($report[$category->id])) {
                        $report[$category->id] = [
                            'category_id' => $category->id,
                            'category_name' => $translation->name, // Use translated name
                            'total_score' => 0,
                            'total_possible_score' => 0,
                            'total_questions' => 0, // Initialize the total questions count
                        ];
                    }

                    // Add the user's score to the category
                    $report[$category->id]['total_score'] += $optionScore;

                    // Step 6: Calculate the total possible score for this category
                    $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
                        $query->whereHas('question.categories', function ($query) use ($category) {
                            $query->where('categories.id', $category->id);
                        });
                    })->sum('percentage'); // Sum up all possible percentages for the category

                    $report[$category->id]['total_possible_score'] = $totalPossibleScore;

                    // Increment the total question count for the category
                    $report[$category->id]['total_questions']++;
                }
            }

            // Step 7: Calculate the percentage score and average percentage for each category
            foreach ($report as &$categoryReport) {
                if ($categoryReport['total_possible_score'] > 0) {
                    // Calculate the percentage score
                    $categoryReport['percentage_score'] =
                        ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;

                    // Calculate the average percentage (total score divided by total number of questions)
                    $categoryReport['average_percentage'] =
                        $categoryReport['total_score'] / $categoryReport['total_questions'];
                } else {
                    $categoryReport['percentage_score'] = 0; // Avoid division by zero
                    $categoryReport['average_percentage'] = 0;
                }
            }

            // Return the report as a JSON response
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'User report fetched successfully!',
                'data' => array_values($report), // Convert associative array to indexed array
            ]);
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadReport(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user_id exists
            'language' => 'required|string', // Ensure the language is provided
        ]);

        try {
            $userId = $request->user_id;
            $language = $request->language;

            // Fetch all user responses
            $responses = Response::where('user_id', $userId)->get();

            $report = [];

            // Process the responses and categories
            foreach ($responses as $response) {
                $questionId = $response->question_id;
                $option = $response->option;
                $optionScore = $option->percentage;

                $categories = Category::whereHas('questions', function ($query) use ($questionId) {
                    $query->where('questions.id', $questionId);
                })->get();

                foreach ($categories as $category) {
                    // Fetch the translation for the category in the specified language
                    $translation = $category->translations()->where('language', $language)->first();

                    if (!$translation) {
                        // Skip if no translation exists for the category in the specified language
                        continue;
                    }

                    if (!isset($report[$category->id])) {
                        $report[$category->id] = [
                            'category_id' => $category->id,
                            'category_name' => $translation->name, // Use translated name
                            'total_score' => 0,
                            'total_possible_score' => 0,
                            'total_questions' => 0,
                        ];
                    }

                    $report[$category->id]['total_score'] += $optionScore;

                    $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
                        $query->whereHas('question.categories', function ($query) use ($category) {
                            $query->where('categories.id', $category->id);
                        });
                    })->sum('percentage');

                    $report[$category->id]['total_possible_score'] = $totalPossibleScore;
                    $report[$category->id]['total_questions']++;
                }
            }

            // Calculate percentage scores for each category
            foreach ($report as &$categoryReport) {
                if ($categoryReport['total_possible_score'] > 0) {
                    $categoryReport['percentage_score'] = ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;
                    $categoryReport['average_percentage'] = $categoryReport['total_score'] / $categoryReport['total_questions'];
                } else {
                    $categoryReport['percentage_score'] = 0;
                    $categoryReport['average_percentage'] = 0;
                }
            }

            // Generate the PDF using a view
            $pdf = FacadePdf::loadView('pdf.report', ['report' => $report, 'language' => $language]);

            // Define file name and path
            $fileName = "user_report_{$userId}.pdf";
            $filePath = '/reports/';

            // Save the PDF to the server using the helper function
            $savedFilePath = Helper::savePdfToServer($pdf->output(), $filePath);

            // Save record in the `reports_downloads` table
            $reportDownload = ReportDownload::create([
                'user_id' => $userId,
                'file' => $savedFilePath,
            ]);

            // Return success response with download details
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'User report file generated successfully!',
                'data' => $reportDownload, // Include report download details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // public function fetchUserReport(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id', // Ensure the user_id exists
    //     ]);

    //     try {
    //         $userId = $request->user_id;

    //         // Step 1: Fetch all user responses
    //         $responses = Response::where('user_id', $userId)->get();

    //         // Step 2: Initialize a report array to store scores for each category
    //         $report = [];

    //         // Step 3: Iterate through the user's responses
    //         foreach ($responses as $response) {
    //             $questionId = $response->question_id;
    //             $option = $response->option; // Get the related option (assumes 'option' relationship in Response model)
    //             $optionScore = $option->percentage; // Get the percentage score from the option

    //             // Step 4: Find all categories linked to the question
    //             $categories = Category::whereHas('questions', function ($query) use ($questionId) {
    //                 $query->where('questions.id', $questionId);
    //             })->get();

    //             // Step 5: Distribute the score to the linked categories
    //             foreach ($categories as $category) {
    //                 if (!isset($report[$category->id])) {
    //                     $report[$category->id] = [
    //                         'category_id' => $category->id,
    //                         'category_name' => $category->name,
    //                         'total_score' => 0,
    //                         'total_possible_score' => 0,
    //                         'total_questions' => 0, // Initialize the total questions count
    //                     ];
    //                 }

    //                 // Add the user's score to the category
    //                 $report[$category->id]['total_score'] += $optionScore;

    //                 // Step 6: Calculate the total possible score for this category
    //                 $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
    //                     $query->whereHas('question.categories', function ($query) use ($category) {
    //                         $query->where('categories.id', $category->id);
    //                     });
    //                 })->sum('percentage'); // Sum up all possible percentages for the category

    //                 $report[$category->id]['total_possible_score'] = $totalPossibleScore;

    //                 // Increment the total question count for the category
    //                 $report[$category->id]['total_questions']++;
    //             }
    //         }

    //         // Step 7: Calculate the percentage score and average percentage for each category
    //         foreach ($report as &$categoryReport) {
    //             if ($categoryReport['total_possible_score'] > 0) {
    //                 // Calculate the percentage score
    //                 $categoryReport['percentage_score'] =
    //                     ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;

    //                 // Calculate the average percentage (total score divided by total number of questions)
    //                 $categoryReport['average_percentage'] =
    //                     $categoryReport['total_score'] / $categoryReport['total_questions'];
    //             } else {
    //                 $categoryReport['percentage_score'] = 0; // Avoid division by zero
    //                 $categoryReport['average_percentage'] = 0;
    //             }
    //         }

    //         // Return the report as a JSON response
    //         return response()->json([
    //             'status_code' => 1, // Success
    //             'message' => 'User report fetched successfully!',
    //             'data' => array_values($report), // Convert associative array to indexed array
    //         ], 200);
    //     } catch (\Exception $e) {
    //         // Handle errors
    //         return response()->json([
    //             'status_code' => 2, // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function fetchQuestionsWithResponses(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user_id exists
            'language' => 'required|string|in:en,de,fr', // Ensure the language is provided and valid
        ]);

        try {
            $userId = $request->user_id;
            $language = $request->language;

            // Step 1: Fetch all user responses
            $responses = Response::where('user_id', $userId)->get();

            // Step 2: Initialize an array to store questions with their responses and categories
            $questionsData = [];

            // Step 3: Iterate through the user's responses
            foreach ($responses as $response) {
                $questionId = $response->question_id;
                $option = $response->option; // Get the selected option (assumes 'option' relationship in Response model)

                // Step 4: Fetch the question details with the required translation for the given language
                $question = Question::find($questionId);

                // Get the question translation based on language
                $questionTranslation = $question->translations()->where('language', $language)->first();

                // Step 5: Fetch the categories associated with this question using translations
                $categories = Category::whereHas('questions', function ($query) use ($questionId) {
                    $query->where('questions.id', $questionId);
                })->get();

                // Prepare the categories data
                // Prepare the categories data, now including the translated 'name' field
                $categoriesData = $categories->map(function ($category) use ($language) {
                    // Fetch category translation for the requested language
                    $categoryTranslation = $category->translations()->where('language', $language)->first();

                    return [
                        'id' => $category->id,
                        'name' => $categoryTranslation ? $categoryTranslation->name : $category->name, // Fallback to default name if no translation is found
                    ];
                });


                // Prepare the question data with response and categories
                $questionsData[] = [
                    'question_id' => $question->id,
                    'question_text' => $questionTranslation ? $questionTranslation->text : null, // Translated text
                    'user_response' => [
                        'option_id' => $option->id,
                        'option_text' => $option->option_label,
                        'option_score' => $option->percentage,
                    ],
                    'categories' => $categoriesData,
                ];
            }

            // Return the data as a JSON response
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'Questions with responses fetched successfully!',
                'data' => $questionsData,
            ], 200);
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // public function downloadReport(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id', // Ensure the user_id exists
    //     ]);



    //     try {
    //         $userId = $request->user_id;

    //         // Fetch all user responses
    //         $responses = Response::where('user_id', $userId)->get();

    //         $report = [];

    //         // Process the responses and categories
    //         foreach ($responses as $response) {
    //             $questionId = $response->question_id;
    //             $option = $response->option;
    //             $optionScore = $option->percentage;

    //             $categories = Category::whereHas('questions', function ($query) use ($questionId) {
    //                 $query->where('questions.id', $questionId);
    //             })->get();

    //             foreach ($categories as $category) {
    //                 if (!isset($report[$category->id])) {
    //                     $report[$category->id] = [
    //                         'category_id' => $category->id,
    //                         'category_name' => $category->name,
    //                         'total_score' => 0,
    //                         'total_possible_score' => 0,
    //                         'total_questions' => 0,
    //                     ];
    //                 }

    //                 $report[$category->id]['total_score'] += $optionScore;

    //                 $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
    //                     $query->whereHas('question.categories', function ($query) use ($category) {
    //                         $query->where('categories.id', $category->id);
    //                     });
    //                 })->sum('percentage');

    //                 $report[$category->id]['total_possible_score'] = $totalPossibleScore;
    //                 $report[$category->id]['total_questions']++;
    //             }
    //         }

    //         // Calculate percentage scores for each category
    //         foreach ($report as &$categoryReport) {
    //             if ($categoryReport['total_possible_score'] > 0) {
    //                 $categoryReport['percentage_score'] = ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;
    //                 $categoryReport['average_percentage'] = $categoryReport['total_score'] / $categoryReport['total_questions'];
    //             } else {
    //                 $categoryReport['percentage_score'] = 0;
    //                 $categoryReport['average_percentage'] = 0;
    //             }
    //         }

    //         // Generate the PDF using a view
    //         $pdf = FacadePdf::loadView('pdf.report', ['report' => $report]);

    //         // Use saveImageToServer to save the generated PDF
    //         $fileName = "user_report_{$userId}";
    //         $filePath = '/reports/';

    //         // Save the PDF to the server using the helper function
    //         $savedFilePath = Helper::savePdfToServer($pdf->output(), $filePath);

    //         // Save record in the `reports_downloads` table
    //         $reportDownload = ReportDownload::create([
    //             'user_id' => $userId,
    //             'file' => $savedFilePath,
    //         ]);

    //         // return public_path() . $savedFilePath, $fileName;
    //         // Return the PDF as a response to download
    //         // return response()->download(public_path() . $savedFilePath, $fileName);
    //         return response()->json([
    //             'status_code' => 1, // Success
    //             'message' => 'User report file fetched successfully!',
    //             'data' => $reportDownload, // Convert associative array to indexed array
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2, // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    //     public function fetchUserReport(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id', // Ensure the user_id exists
    //     ]);

    //     try {
    //         $userId = $request->user_id;

    //         // Step 1: Fetch all user responses
    //         $responses = Response::where('user_id', $userId)->get();

    //         // Step 2: Initialize a report array to store scores for each category
    //         $report = [];

    //         // Step 3: Iterate through the user's responses
    //         foreach ($responses as $response) {
    //             $questionId = $response->question_id;
    //             $option = $response->option; // Get the related option (assumes 'option' relationship in Response model)
    //             $optionScore = $option->percentage; // Get the percentage score from the option

    //             // Step 4: Find all categories linked to the question
    //             $categories = Category::whereHas('questions', function ($query) use ($questionId) {
    //                 $query->where('questions.id', $questionId);
    //             })->get();


    //             // Step 5: Distribute the score to the linked categories
    //             foreach ($categories as $category) {
    //                 if (!isset($report[$category->id])) {
    //                     $report[$category->id] = [
    //                         'category_id' => $category->id,
    //                         'category_name' => $category->name,
    //                         'total_score' => 0,
    //                         'total_possible_score' => 0,
    //                     ];
    //                 }

    //                 // Add the user's score to the category
    //                 $report[$category->id]['total_score'] += $optionScore;

    //                 // Step 6: Calculate the total possible score for this category
    //                 $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
    //                     $query->whereHas('question.categories', function ($query) use ($category) {
    //                         $query->where('categories.id', $category->id);
    //                     });
    //                 })->sum('percentage'); // Sum up all possible percentages for the category

    //                 $report[$category->id]['total_possible_score'] = $totalPossibleScore;
    //             }
    //         }



    //         // Step 7: Calculate the percentage score for each category
    //         foreach ($report as &$categoryReport) {
    //             if ($categoryReport['total_possible_score'] > 0) {
    //                 $categoryReport['percentage_score'] =
    //                     ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;
    //             } else {
    //                 $categoryReport['percentage_score'] = 0; // Avoid division by zero
    //             }
    //         }

    //         // Return the report as a JSON response
    //         return response()->json([
    //             'status_code' => 1, // Success
    //             'message' => 'User report fetched successfully!',
    //             'data' => array_values($report), // Convert associative array to indexed array
    //         ], 200);

    //     } catch (\Exception $e) {
    //         // Handle errors
    //         return response()->json([
    //             'status_code' => 2, // Failure
    //             'message' => 'Something went wrong. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

}
