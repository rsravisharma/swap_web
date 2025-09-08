<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalAgreement;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class LegalController extends Controller
{
    // Cache duration in minutes (24 hours)
    private const CACHE_DURATION = 1440;

    /**
     * Get privacy policy
     * GET /legal/privacy-policy
     */
    public function getPrivacyPolicy(): JsonResponse
    {
        try {
            $content = Cache::remember('privacy_policy_content', self::CACHE_DURATION, function () {
                $document = LegalDocument::where('type', 'privacy_policy')
                    ->where('is_active', true)
                    ->latest('version')
                    ->first();

                if (!$document) {
                    return [
                        'content' => $this->getDefaultPrivacyPolicy(),
                        'last_updated' => now()->format('Y-m-d H:i:s'),
                        'version' => '1.0'
                    ];
                }

                return [
                    'content' => $document->content,
                    'last_updated' => $document->updated_at->format('Y-m-d H:i:s'),
                    'version' => $document->version
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch privacy policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get terms and conditions
     * GET /legal/terms-and-conditions
     */
    public function getTermsAndConditions(): JsonResponse
    {
        try {
            $content = Cache::remember('terms_conditions_content', self::CACHE_DURATION, function () {
                $document = LegalDocument::where('type', 'terms_and_conditions')
                    ->where('is_active', true)
                    ->latest('version')
                    ->first();

                if (!$document) {
                    return [
                        'content' => $this->getDefaultTermsAndConditions(),
                        'last_updated' => now()->format('Y-m-d H:i:s'),
                        'version' => '1.0'
                    ];
                }

                return [
                    'content' => $document->content,
                    'last_updated' => $document->updated_at->format('Y-m-d H:i:s'),
                    'version' => $document->version
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch terms and conditions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get legal document by type
     * GET /legal/document/{documentType}
     */
    public function getLegalDocument(string $documentType): JsonResponse
    {
        $validTypes = ['privacy_policy', 'terms_and_conditions', 'refund_policy', 'cancellation_policy', 'cookie_policy'];

        if (!in_array($documentType, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document type'
            ], 400);
        }

        try {
            $cacheKey = "legal_document_{$documentType}";

            $content = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($documentType) {
                $document = LegalDocument::where('type', $documentType)
                    ->where('is_active', true)
                    ->latest('version')
                    ->first();

                if (!$document) {
                    return [
                        'content' => $this->getDefaultDocument($documentType),
                        'last_updated' => now()->format('Y-m-d H:i:s'),
                        'version' => '1.0'
                    ];
                }

                return [
                    'content' => $document->content,
                    'last_updated' => $document->updated_at->format('Y-m-d H:i:s'),
                    'version' => $document->version,
                    'title' => $document->title
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit legal agreement
     * POST /legal/agreement
     */
    public function submitLegalAgreement(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|in:privacy_policy,terms_and_conditions,refund_policy,cancellation_policy',
            'accepted_at' => 'required|date',
            'version' => 'nullable|string',
            'signature' => 'nullable|string|max:255',
            'user_ip' => 'nullable|ip',
            'user_agent' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $data = $validator->validated();
            $data['user_id'] = $user->id;
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $agreement = LegalAgreement::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'document_type' => $data['document_type']
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Legal agreement submitted successfully',
                'data' => $agreement
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit agreement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user agreements
     * GET /user/agreements
     */
    public function getUserAgreements(): JsonResponse
    {
        try {
            $user = Auth::user();

            $agreements = LegalAgreement::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $agreements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agreements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept terms and conditions
     * POST /legal/accept-terms
     */
    public function acceptTermsAndConditions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'accepted_at' => 'required|date',
            'version' => 'nullable|string',
            'signature' => 'nullable|string|max:255',
            'user_ip' => 'nullable|ip',
            'user_agent' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $data = $validator->validated();
            $data['user_id'] = $user->id;
            $data['document_type'] = 'terms_and_conditions';
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $agreement = LegalAgreement::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'document_type' => 'terms_and_conditions'
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions accepted successfully',
                'data' => $agreement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept terms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept privacy policy
     * POST /legal/accept-privacy
     */
    public function acceptPrivacyPolicy(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'accepted_at' => 'required|date',
            'version' => 'nullable|string',
            'signature' => 'nullable|string|max:255',
            'user_ip' => 'nullable|ip',
            'user_agent' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $data = $validator->validated();
            $data['user_id'] = $user->id;
            $data['document_type'] = 'privacy_policy';
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $agreement = LegalAgreement::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'document_type' => 'privacy_policy'
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Privacy policy accepted successfully',
                'data' => $agreement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept privacy policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default privacy policy content
     */
    private function getDefaultPrivacyPolicy(): string
    {
        return "
# Privacy Policy

Last updated: " . now()->format('F d, Y') . "

## Information We Collect

We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support.

## How We Use Your Information

We use the information we collect to provide, maintain, and improve our services.

## Information Sharing and Disclosure

We do not sell, trade, or rent your personal information to third parties.

## Data Security

We implement appropriate security measures to protect your personal information.

## Contact Us

If you have any questions about this Privacy Policy, please contact us.
        ";
    }

    /**
     * Get default terms and conditions content
     */
    private function getDefaultTermsAndConditions(): string
    {
        return "
# Terms and Conditions

Last updated: " . now()->format('F d, Y') . "

## Acceptance of Terms

By accessing and using this service, you accept and agree to be bound by the terms and provision of this agreement.

## Use License

Permission is granted to temporarily use this service for personal, non-commercial transitory viewing only.

## Disclaimer

The materials on this service are provided on an 'as is' basis. We make no warranties, expressed or implied.

## Limitations

In no event shall our company be liable for any damages arising out of the use or inability to use the materials on this service.

## Contact Information

If you have any questions about these Terms and Conditions, please contact us.
        ";
    }

    /**
     * Get default document content based on type
     */
    private function getDefaultDocument(string $type): string
    {
        return match ($type) {
            'privacy_policy' => $this->getDefaultPrivacyPolicy(),
            'terms_and_conditions' => $this->getDefaultTermsAndConditions(),
            'refund_policy' => "# Refund Policy\n\nLast updated: " . now()->format('F d, Y') . "\n\nRefund policy content...",
            'cancellation_policy' => "# Cancellation Policy\n\nLast updated: " . now()->format('F d, Y') . "\n\nCancellation policy content...",
            'cookie_policy' => "# Cookie Policy\n\nLast updated: " . now()->format('F d, Y') . "\n\nCookie policy content...",
            default => "# Legal Document\n\nDocument content not available."
        };
    }
}
