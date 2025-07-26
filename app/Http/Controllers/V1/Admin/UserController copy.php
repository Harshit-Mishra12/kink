<?php

namespace App\Http\Controllers\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Response;
use App\Models\TermsAndConditions;
use App\Models\User;
use App\Models\UserBankDetails;
use App\Models\UserDocuments;
use Exception;
use Illuminate\Http\Request;



class UserController extends Controller
{
    public function fetchUsers(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'filters' => 'nullable|array', // Filters must be an array
            'filters.*.key' => 'required|string|in:language,gender,orientation,country,age_category', // Allowed keys
            'filters.*.value' => 'required|string', // Corresponding values
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            // Default pagination parameters
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $filters = $request->input('filters', []);

            // Query to fetch users with role 'ANONYMOUSUSER' and responses
            $query = User::where('role', 'ANONYMOUSUSER')
                ->whereHas('responses') // Ensure the user has at least one response
                ->when(!empty($filters), function ($q) use ($filters) {
                    foreach ($filters as $filter) {
                        $filterKey = $filter['key'];
                        $filterValue = $filter['value'];

                        // Apply filter dynamically
                        $q->where($filterKey, $filterValue);
                    }
                })
                ->orderBy('created_at', 'desc'); // Order by latest users first

            // Paginate the results
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            // Return success response
            return response()->json([
                'status_code' => 1, // Success
                'message' => 'Users fetched successfully!',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors and return failure response
            return response()->json([
                'status_code' => 2, // Failure
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
    public function fetchUserAnalysis(Request $request)
    {
        $request->validate([
            'language' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filters' => 'nullable|array',
            'filters.*.key' => 'required|string|in:language,gender,orientation,country,age_category',
            'filters.*.value' => 'required|string',
        ]);

        $language = $request->input('language', 'en');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $filters = $request->input('filters', []);

        try {
            // Base query for anonymous users with responses
            $userQuery = User::where('role', 'ANONYMOUSUSER')
                ->whereHas('responses', function ($query) use ($startDate, $endDate) {
                    if ($startDate) {
                        $query->where('created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query->where('created_at', '<=', $endDate . ' 23:59:59');
                    }
                });

            // Apply filters directly to users table
            foreach ($filters as $filter) {
                $key = $filter['key'];
                $value = $filter['value'];

                // Special handling for age_category if needed (exact match)
                $userQuery->where($key, $value);
            }

            $userIds = $userQuery->pluck('id');

            if ($userIds->isEmpty()) {
                return response()->json([
                    'status_code' => 1,
                    'message' => 'No users found matching the criteria.',
                    'data' => []
                ], 200);
            }

            // Preload all necessary data in bulk
            $responses = Response::with([
                'option',
                'question.categories.translations' => function ($q) use ($language) {
                    $q->where('language', $language);
                }
            ])
                ->whereIn('user_id', $userIds)
                // ->when($startDate, function($q) use ($startDate) {
                //     $q->where('created_at', '>=', $startDate);
                // })
                // ->when($endDate, function($q) use ($endDate) {
                //     $q->where('created_at', '<=', $endDate . ' 23:59:59');
                // })
                ->get()
                ->groupBy('user_id');

            $categorySums = [];
            $processedUsers = 0;

            // Process user responses in memory
            foreach ($responses as $userId => $userResponses) {
                $userReport = $this->processUserResponses($userResponses);

                if (!empty($userReport)) {
                    $processedUsers++;

                    foreach ($userReport as $categoryId => $category) {
                        if (!isset($categorySums[$categoryId])) {
                            $categorySums[$categoryId] = [
                                'category_id' => $categoryId,
                                'category_name' => $category['category_name'],
                                'total_average' => 0,
                                'user_count' => 0,
                            ];
                        }

                        $categorySums[$categoryId]['total_average'] += $category['average_percentage'];
                        $categorySums[$categoryId]['user_count']++;
                    }
                }
            }

            // Calculate averages
            $categoryAverages = [];
            foreach ($categorySums as $category) {
                $categoryAverages[] = [
                    'category_id' => $category['category_id'],
                    'category_name' => $category['category_name'],
                    'category_average_percentage' => round($category['total_average'] / $category['user_count'], 2),
                ];
            }

            return response()->json([
                'status_code' => 1,
                'message' => 'Category average percentage calculated successfully.',
                'data' => $categoryAverages,
                'meta' => [
                    'total_users_processed' => $processedUsers,
                    'total_categories' => count($categoryAverages),
                    'filters_applied' => [
                        'language' => $language,
                        'date_range' => $startDate ? [$startDate, $endDate] : null,
                        'additional_filters' => $filters
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Failed to calculate category averages.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function processUserResponses($responses)
    {
        $report = [];

        foreach ($responses as $response) {
            if (!$response->option || !$response->question) {
                continue;
            }

            $optionScore = $response->option->percentage;
            $categories = $response->question->categories;

            foreach ($categories as $category) {
                // Skip if no translation exists
                if ($category->translations->isEmpty()) {
                    continue;
                }

                $translation = $category->translations->first();
                $categoryId = $category->id;

                if (!isset($report[$categoryId])) {
                    $report[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $translation->name,
                        'total_score' => 0,
                        'total_questions' => 0,
                    ];
                }

                $report[$categoryId]['total_score'] += $optionScore;
                $report[$categoryId]['total_questions']++;
            }
        }

        // Calculate averages
        foreach ($report as &$category) {
            $category['average_percentage'] = $category['total_questions'] > 0
                ? $category['total_score'] / $category['total_questions']
                : 0;
        }

        return $report;
    }
    //     public function fetchUserAnalysis(Request $request)
    // {
    //     $language = $request->input('language', 'en'); // Default to English if not provided

    //     try {
    //         // Step 1: Get all users with role 'ANONYMOUSUSER' and at least one response
    //         $users = User::where('role', 'ANONYMOUSUSER')
    //                     ->whereHas('responses')
    //                     ->get();

    //         $categorySums = [];
    //         $userCount = 0;

    //         foreach ($users as $user) {
    //             $response = $this->fetchUserReportInternal($user->id, $language); // Internal version returns array, not JSON
    //             if ($response && isset($response['data'])) {
    //                 $userCount++;

    //                 foreach ($response['data'] as $category) {
    //                     $categoryId = $category['category_id'];
    //                     $categoryName = $category['category_name'];
    //                     $average = $category['average_percentage'];

    //                     if (!isset($categorySums[$categoryId])) {
    //                         $categorySums[$categoryId] = [
    //                             'category_id' => $categoryId,
    //                             'category_name' => $categoryName,
    //                             'total_average' => 0,
    //                             'user_count' => 0,
    //                         ];
    //                     }

    //                     $categorySums[$categoryId]['total_average'] += $average;
    //                     $categorySums[$categoryId]['user_count']++;
    //                 }
    //             }
    //         }

    //         // Step 2: Calculate average per category
    //         $categoryAverages = [];

    //         foreach ($categorySums as $cat) {
    //             $categoryAverages[] = [
    //                 'category_id' => $cat['category_id'],
    //                 'category_name' => $cat['category_name'],
    //                 'category_average_percentage' => round($cat['total_average'] / $cat['user_count'], 2),
    //             ];
    //         }

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'Category average percentage calculated successfully.',
    //             'data' => $categoryAverages
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Failed to calculate category averages.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    // private function fetchUserReportInternal($user_id, $language)
    // {
    //     try {
    //         $responses = Response::where('user_id', $user_id)->get();

    //         $report = [];

    //         foreach ($responses as $response) {
    //             $questionId = $response->question_id;
    //             $option = $response->option;
    //             $optionScore = $option->percentage;

    //             $categories = Category::whereHas('questions', function ($query) use ($questionId) {
    //                 $query->where('questions.id', $questionId);
    //             })->get();

    //             foreach ($categories as $category) {
    //                 $translation = $category->translations()->where('language', $language)->first();
    //                 if (!$translation) continue;

    //                 if (!isset($report[$category->id])) {
    //                     $report[$category->id] = [
    //                         'category_id' => $category->id,
    //                         'category_name' => $translation->name,
    //                         'total_score' => 0,
    //                         'total_possible_score' => 0,
    //                         'total_questions' => 0,
    //                     ];
    //                 }

    //                 $report[$category->id]['total_score'] += $optionScore;
    //                 $report[$category->id]['total_possible_score'] += 100; // Assume 100 is the max per question
    //                 $report[$category->id]['total_questions']++;
    //             }
    //         }

    //         foreach ($report as &$categoryReport) {
    //             if ($categoryReport['total_possible_score'] > 0) {
    //                 $categoryReport['percentage_score'] =
    //                     ($categoryReport['total_score'] / $categoryReport['total_possible_score']) * 100;
    //                 $categoryReport['average_percentage'] =
    //                     $categoryReport['total_score'] / $categoryReport['total_questions'];
    //             } else {
    //                 $categoryReport['percentage_score'] = 0;
    //                 $categoryReport['average_percentage'] = 0;
    //             }
    //         }

    //         return [
    //             'status_code' => 1,
    //             'data' => array_values($report),
    //         ];
    //     } catch (\Exception $e) {
    //         return null; // fail silently for individual user
    //     }
    // }


    public function fetchUserAnalysis3(Request $request)
    {

        $request->validate([
            'filters' => 'nullable|array',
            'filters.*.key' => 'required|string|in:language,gender,orientation,country,age_category',
            'filters.*.value' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            // 'language' => 'required|string|max:10',
        ]);
        // return "yes";

        try {
            $filters = $request->input('filters', []);
            $language = $request->input('language');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Fetch filtered users
            $usersQuery = User::where('role', 'ANONYMOUSUSER')
                ->whereHas('responses', function ($query) use ($startDate, $endDate) {
                    if ($startDate) {
                        $query->whereDate('created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query->whereDate('created_at', '<=', $endDate);
                    }
                });

            foreach ($filters as $filter) {
                $usersQuery->where($filter['key'], $filter['value']);
            }

            $users = $usersQuery->get();

            $categoryStats = []; // aggregate for org-wide average
            $userCount = $users->count();

            foreach ($users as $user) {
                // Collect report per user
                $userResponses = Response::with(['option', 'question.categories.translations'])
                    ->where('user_id', $user->id)
                    ->get();

                $userCategoryScores = [];

                foreach ($userResponses as $response) {
                    foreach ($response->question->categories as $category) {
                        $categoryId = $category->id;
                        $translatedName = $category->translations->where('language', $language)->first()->name ?? $category->name;

                        if (!isset($userCategoryScores[$categoryId])) {
                            $userCategoryScores[$categoryId] = [
                                'category_id' => $categoryId,
                                'category_name' => $translatedName,
                                'total_score' => 0,
                                'total_questions' => 0,
                            ];
                        }

                        $userCategoryScores[$categoryId]['total_score'] += $response->option->percentage ?? 0;
                        $userCategoryScores[$categoryId]['total_questions'] += 1;
                    }
                }

                foreach ($userCategoryScores as $catId => $data) {
                    if ($data['total_questions'] == 0) continue;

                    $averagePercentage = $data['total_score'] / $data['total_questions'];

                    if (!isset($categoryStats[$catId])) {
                        $categoryStats[$catId] = [
                            'category_id' => $data['category_id'],
                            'category_name' => $data['category_name'],
                            'total_users' => 0,
                            'sum_user_avg_percentage' => 0,
                        ];
                    }

                    $categoryStats[$catId]['total_users'] += 1;
                    $categoryStats[$catId]['sum_user_avg_percentage'] += $averagePercentage;
                }
            }

            // Final format for output
            $finalStats = [];
            foreach ($categoryStats as $stat) {
                $finalStats[] = [
                    'category_id' => $stat['category_id'],
                    'category_name' => $stat['category_name'],
                    'average_category_percentage' => round($stat['sum_user_avg_percentage'] / $stat['total_users'], 2),
                ];
            }

            return response()->json([
                'status_code' => 1,
                'message' => 'User analysis calculated successfully!',
                'data' => $finalStats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchUserAnalysis2(Request $request)
    {
        $request->validate([
            'filters' => 'nullable|array',
            'filters.*.key' => 'required|string|in:language,gender,orientation,country,age_category',
            'filters.*.value' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            $filters = $request->input('filters', []);
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $language = collect($filters)->firstWhere('key', 'language')['value'] ?? 'en';

            // Query users
            $userQuery = User::where('role', 'ANONYMOUSUSER')
                ->whereHas('responses') // Must have at least one response
                ->when(!empty($filters), function ($q) use ($filters) {
                    foreach ($filters as $filter) {
                        $q->where($filter['key'], $filter['value']);
                    }
                })
                ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
                ->orderBy('created_at', 'desc');

            $users = $userQuery->get(['id']);

            if ($users->isEmpty()) {
                return response()->json([
                    'status_code' => 1,
                    'message' => 'No users found.',
                    'data' => [],
                ]);
            }

            // Preload everything for performance
            $userIds = $users->pluck('id');
            $responses = Response::whereIn('user_id', $userIds)->get();
            $options = Option::all()->keyBy('id');
            $questionsWithCategories = Question::with('categories.translations')->get()->keyBy('id');

            // Initialize org-wide report
            $orgReport = [];

            foreach ($userIds as $userId) {
                $userResponses = $responses->where('user_id', $userId);

                foreach ($userResponses as $response) {
                    $optionScore = $options[$response->option_id]->percentage ?? 0;
                    $question = $questionsWithCategories[$response->question_id] ?? null;
                    if (!$question) continue;

                    foreach ($question->categories as $category) {
                        $categoryName = $category->translations->where('language', $language)->first()?->name ?? $category->name;

                        if (!isset($orgReport[$category->id])) {
                            $orgReport[$category->id] = [
                                'category_id' => $category->id,
                                'category_name' => $categoryName,
                                'total_score' => 0,
                                'total_possible_score' => 0,
                                'total_questions' => 0,
                            ];
                        }

                        $orgReport[$category->id]['total_score'] += $optionScore;
                        $orgReport[$category->id]['total_possible_score'] += 100; // since max is 100 per response
                        $orgReport[$category->id]['total_questions'] += 1;
                    }
                }
            }

            // Calculate averages
            foreach ($orgReport as &$cat) {
                if ($cat['total_possible_score'] > 0) {
                    $cat['percentage_score'] = ($cat['total_score'] / $cat['total_possible_score']) * 100;
                    $cat['average_percentage'] = $cat['total_score'] / $cat['total_questions'];
                } else {
                    $cat['percentage_score'] = 0;
                    $cat['average_percentage'] = 0;
                }
            }

            return response()->json([
                'status_code' => 1,
                'message' => 'Organizational user analysis fetched successfully!',
                'data' => array_values($orgReport),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 2,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // public function fetchUserAnalysis(Request $request)
    // {
    //     // return "yes";
    //     // Validate the request
    //     $request->validate([
    //         'filters' => 'nullable|array',
    //         'filters.*.key' => 'required|string|in:language,gender,orientation,country,age_category',
    //         'filters.*.value' => 'required|string',
    //         'start_date' => 'nullable|date',
    //         'end_date' => 'nullable|date|after_or_equal:start_date',
    //     ]);

    //     try {
    //         $filters = $request->input('filters', []);
    //         $startDate = $request->input('start_date');
    //         $endDate = $request->input('end_date');
    //         $language = collect($filters)->firstWhere('key', 'language')['value'] ?? 'en';

    //         // Fetch all matched users
    //         $users = User::where('role', 'ANONYMOUSUSER')
    //             ->whereHas('responses')
    //             ->when(!empty($filters), function ($q) use ($filters) {
    //                 foreach ($filters as $filter) {
    //                     $q->where($filter['key'], $filter['value']);
    //                 }
    //             })
    //             ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
    //             ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $categoryTotals = [];



    //         foreach ($users as $user) {
    //             $responses = Response::where('user_id', $user->id)->get();
    //             $report = [];

    //             foreach ($responses as $response) {
    //                 $questionId = $response->question_id;
    //                 $option = $response->option;
    //                 if (!$option) continue;

    //                 $optionScore = $option->percentage;

    //                 $categories = Category::whereHas('questions', function ($query) use ($questionId) {
    //                     $query->where('questions.id', $questionId);
    //                 })->get();

    //                 foreach ($categories as $category) {
    //                     $translation = $category->translations()->where('language', $language)->first();
    //                     if (!$translation) continue;

    //                     if (!isset($report[$category->id])) {
    //                         $report[$category->id] = [
    //                             'category_id' => $category->id,
    //                             'category_name' => $translation->name,
    //                             'total_score' => 0,
    //                             'total_possible_score' => 0,
    //                             'total_questions' => 0,
    //                         ];
    //                     }

    //                     $report[$category->id]['total_score'] += $optionScore;

    //                     $totalPossibleScore = Option::whereHas('responses', function ($query) use ($category) {
    //                         $query->whereHas('question.categories', function ($q) use ($category) {
    //                             $q->where('categories.id', $category->id);
    //                         });
    //                     })->sum('percentage');

    //                     $report[$category->id]['total_possible_score'] = $totalPossibleScore;
    //                     $report[$category->id]['total_questions']++;
    //                 }
    //             }

    //             foreach ($report as $catId => $categoryReport) {
    //                 if ($categoryReport['total_possible_score'] > 0) {
    //                     $averagePercentage = $categoryReport['total_questions'] > 0
    //                         ? $categoryReport['total_score'] / $categoryReport['total_questions']
    //                         : 0;

    //                     if (!isset($categoryTotals[$catId])) {
    //                         $categoryTotals[$catId] = [
    //                             'category_id' => $catId,
    //                             'category_name' => $categoryReport['category_name'],
    //                             'total_average_percentage' => 0,
    //                             'count' => 0,
    //                         ];
    //                     }

    //                     $categoryTotals[$catId]['total_average_percentage'] += $averagePercentage;
    //                     $categoryTotals[$catId]['count'] += 1;
    //                 }
    //             }
    //         }

    //         $finalCategoryAverages = array_map(function ($item) {
    //             return [
    //                 'category_id' => $item['category_id'],
    //                 'category_name' => $item['category_name'],
    //                 'average_percentage' => round($item['total_average_percentage'] / $item['count'], 2),
    //             ];
    //         }, $categoryTotals);

    //         return response()->json([
    //             'status_code' => 1,
    //             'message' => 'User analysis and category averages fetched successfully!',
    //             'total_users' => $users->count(),
    //             'category_averages' => array_values($finalCategoryAverages),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status_code' => 2,
    //             'message' => 'Something went wrong.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
