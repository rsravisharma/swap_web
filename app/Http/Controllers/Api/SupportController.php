<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use App\Models\User;
use App\Models\SupportRequest;
use App\Models\Faq;
// use App\Models\Announcement;
// use App\Models\BugReport;
// use App\Models\Feedback;
// use App\Models\FeatureRequest;
// use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SupportController extends Controller
{
    /**
     * Get current user profile
     * GET /user/profile
     */
    public function getUserProfile(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user profile'
            ], 500);
        }
    }


    /**
     * Submit support request
     * POST /support/requests
     */
    public function submitSupportRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => 'sometimes|string|in:technical,account,payment,general,bug',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            $supportRequest = SupportRequest::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'category' => $request->input('category', 'general'),
                'priority' => $request->input('priority', 'medium'),
                'status' => 'pending'
            ]);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('support_attachments', 'public');
                    $supportRequest->attachments()->create([
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $supportRequest->load('attachments'),
                'message' => 'Support request submitted successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to submit support request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit support request'
            ], 500);
        }
    }

    /**
     * Get user's support requests
     * GET /support/requests/my-requests
     */
    public function getMySupportRequests(): JsonResponse
    {
        try {
            $user = Auth::user();

            $requests = SupportRequest::where('user_id', $user->id)
                ->with(['attachments'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get support requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve support requests'
            ], 500);
        }
    }

    /**
     * Get support request details
     * GET /support/requests/{requestId}
     */
    public function getSupportRequestDetails(string $requestId): JsonResponse
    {
        try {
            $user = Auth::user();

            $request = SupportRequest::with(['attachments', 'responses'])
                ->where('id', $requestId)
                ->where('user_id', $user->id)
                ->first();

            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $request
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get support request details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve support request details'
            ], 500);
        }
    }

    /**
     * Get FAQs
     * GET /support/faqs
     */
    public function getFaqs(): JsonResponse
    {
        try {
            $faqs = Cache::remember('faqs', 3600, function () {
                return Faq::where('is_active', true)
                    ->orderBy('category')
                    ->orderBy('sort_order')
                    ->get();
            });

            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get FAQs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve FAQs'
            ], 500);
        }
    }

    /**
     * Mark FAQ as helpful
     * POST /support/faqs/{faqId}/helpful
     */
    public function markFaqHelpful(Request $request, string $faqId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'helpful' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $faq = Faq::find($faqId);

            if (!$faq) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ not found'
                ], 404);
            }

            $isHelpful = $request->boolean('helpful');

            // Check if user already marked this FAQ
            $user = Auth::user();
            $existingMark = DB::table('faq_helpful_marks')
                ->where('faq_id', $faqId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingMark) {
                if ($existingMark->is_helpful != $isHelpful) {
                    DB::table('faq_helpful_marks')
                        ->where('faq_id', $faqId)
                        ->where('user_id', $user->id)
                        ->update(['is_helpful' => $isHelpful]);

                    $increment = $isHelpful ? 2 : -2; // Change from opposite
                    $faq->increment('helpful_count', $increment);
                }
            } else {
                DB::table('faq_helpful_marks')->insert([
                    'faq_id' => $faqId,
                    'user_id' => $user->id,
                    'is_helpful' => $isHelpful,
                    'created_at' => now()
                ]);

                $increment = $isHelpful ? 1 : -1;
                $faq->increment('helpful_count', $increment);
            }

            return response()->json([
                'success' => true,
                'message' => 'FAQ feedback recorded'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark FAQ helpful: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record FAQ feedback'
            ], 500);
        }
    }

    /**
     * Search FAQs
     * GET /support/faqs/search
     */
    public function searchFaqs(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = $request->input('q');

            $faqs = Faq::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('question', 'like', "%{$query}%")
                        ->orWhere('answer', 'like', "%{$query}%")
                        ->orWhere('tags', 'like', "%{$query}%");
                })
                ->orderBy('category')
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to search FAQs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search FAQs'
            ], 500);
        }
    }

    /**
     * Submit feedback
     * POST /support/feedback
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'rating' => 'nullable|integer|between:1,5',
            'category' => 'sometimes|string|in:app,service,feature,bug,other'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Feedback::create([
            //     'user_id' => $user->id,
            //     'message' => $request->message,
            //     'rating' => $request->rating,
            //     'category' => $request->input('category', 'general')
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback submitted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to submit feedback: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit feedback'
            ], 500);
        }
    }

    /**
     * Get contact information
     * GET /support/contact
     */
    public function getContactInfo(): JsonResponse
    {
        try {
            $contactInfo = [
                'email' => config('app.support_email', 'support@example.com'),
                'phone' => config('app.support_phone', '+1-555-0123'),
                'hours' => 'Monday - Friday, 9:00 AM - 6:00 PM EST',
                'address' => '123 Main St, City, State 12345',
                'website' => config('app.url'),
                'social_media' => [
                    'twitter' => '@example',
                    'facebook' => 'example',
                    'linkedin' => 'company/example'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $contactInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get contact info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contact information'
            ], 500);
        }
    }

    /**
     * Get app information
     * GET /support/app-info
     */
    public function getAppInfo(): JsonResponse
    {
        try {
            $appInfo = [
                'version' => config('app.version', '1.0.0'),
                'build' => config('app.build', '100'),
                'environment' => config('app.env'),
                'last_updated' => config('app.last_updated', now()->toDateString()),
                'platform_support' => [
                    'ios' => ['min_version' => '12.0'],
                    'android' => ['min_sdk' => '21'],
                    'web' => ['browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge']]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $appInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get app info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve app information'
            ], 500);
        }
    }

    /**
     * Get system status
     * GET /support/system-status
     */
    public function getSystemStatus(): JsonResponse
    {
        try {
            $systemStatus = [
                'status' => 'operational',
                'services' => [
                    'api' => 'operational',
                    'database' => 'operational',
                    'storage' => 'operational',
                    'payments' => 'operational',
                    'notifications' => 'operational'
                ],
                'last_updated' => now()->toISOString(),
                'incidents' => []
            ];

            return response()->json([
                'success' => true,
                'data' => $systemStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system status'
            ], 500);
        }
    }

    /**
     * Get announcements
     * GET /support/announcements
     */
    // public function getAnnouncements(): JsonResponse
    // {
    //     try {
    //         $announcements = Announcement::where('is_active', true)
    //             ->where('start_date', '<=', now())
    //             ->where(function ($query) {
    //                 $query->whereNull('end_date')
    //                     ->orWhere('end_date', '>=', now());
    //             })
    //             ->orderBy('priority', 'desc')
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'data' => $announcements
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to get announcements: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve announcements'
    //         ], 500);
    //     }
    // }

    /**
     * Submit bug report
     * POST /support/bug-reports
     */
    public function submitBugReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'steps_to_reproduce' => 'nullable|string|max:2000',
            'expected_behavior' => 'nullable|string|max:1000',
            'actual_behavior' => 'nullable|string|max:1000',
            'platform' => 'required|string',
            'app_version' => 'required|string',
            'device_info' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // BugReport::create([
            //     'user_id' => $user->id,
            //     'title' => $request->title,
            //     'description' => $request->description,
            //     'steps_to_reproduce' => $request->steps_to_reproduce,
            //     'expected_behavior' => $request->expected_behavior,
            //     'actual_behavior' => $request->actual_behavior,
            //     'platform' => $request->platform,
            //     'app_version' => $request->app_version,
            //     'device_info' => $request->device_info,
            //     'status' => 'open'
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Bug report submitted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to submit bug report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit bug report'
            ], 500);
        }
    }

    /**
     * Rate support experience
     * POST /support/requests/{requestId}/rate
     */
    public function rateSupportExperience(Request $request, string $requestId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'feedback' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            $supportRequest = SupportRequest::where('id', $requestId)
                ->where('user_id', $user->id)
                ->first();

            if (!$supportRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support request not found'
                ], 404);
            }

            $supportRequest->update([
                'rating' => $request->rating,
                'rating_feedback' => $request->feedback,
                'rated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to rate support experience: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating'
            ], 500);
        }
    }

    /**
     * Submit feature request
     * POST /support/feature-requests
     */
    public function submitFeatureRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'use_case' => 'nullable|string|max:2000',
            'priority' => 'sometimes|string|in:low,medium,high'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // FeatureRequest::create([
            //     'user_id' => $user->id,
            //     'title' => $request->title,
            //     'description' => $request->description,
            //     'use_case' => $request->use_case,
            //     'priority' => $request->input('priority', 'medium'),
            //     'status' => 'pending'
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature request submitted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to submit feature request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit feature request'
            ], 500);
        }
    }

    /**
     * Get popular support topics
     * GET /support/popular-topics
     */
    public function getPopularTopics(): JsonResponse
    {
        try {
            $popularTopics = Cache::remember('popular_support_topics', 1800, function () {
                return DB::table('support_requests')
                    ->select('category', DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($topic) {
                        return [
                            'category' => $topic->category,
                            'name' => ucfirst(str_replace('_', ' ', $topic->category)),
                            'count' => $topic->count
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'data' => $popularTopics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get popular topics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular topics'
            ], 500);
        }
    }
}
