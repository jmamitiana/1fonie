<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\Review;
use App\Models\Company;
use App\Models\Notification;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Mission $mission)
    {
        $user = $request->user();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Determine review type and validate
        if ($user->role === 'company') {
            if ($mission->company_id !== $user->company->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if ($mission->status !== 'completed') {
                return response()->json(['message' => 'Can only review completed missions'], 400);
            }
            if (!$mission->provider_id) {
                return response()->json(['message' => 'No provider assigned to this mission'], 400);
            }

            // Check if already reviewed
            $existingReview = Review::where('mission_id', $mission->id)
                ->where('reviewer_id', $user->id)
                ->first();

            if ($existingReview) {
                return response()->json(['message' => 'Already reviewed this mission'], 400);
            }

            $review = Review::create([
                'mission_id' => $mission->id,
                'reviewer_id' => $user->id,
                'reviewee_id' => $mission->provider->user_id,
                'provider_id' => $mission->provider_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'type' => 'company_to_provider',
            ]);

            // Update provider rating
            $this->updateProviderRating($mission->provider_id);

            // Notify provider
            Notification::createForUser(
                $mission->provider->user_id,
                'new_review',
                'New Review',
                "You received a {$request->rating}-star review for '{$mission->title}'",
                ['mission_id' => $mission->id, 'review_id' => $review->id]
            );

        } elseif ($user->role === 'provider') {
            if ($mission->provider_id !== $user->provider->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if ($mission->status !== 'completed') {
                return response()->json(['message' => 'Can only review completed missions'], 400);
            }

            // Check if already reviewed
            $existingReview = Review::where('mission_id', $mission->id)
                ->where('reviewer_id', $user->id)
                ->first();

            if ($existingReview) {
                return response()->json(['message' => 'Already reviewed this mission'], 400);
            }

            $review = Review::create([
                'mission_id' => $mission->id,
                'reviewer_id' => $user->id,
                'reviewee_id' => $mission->company->user_id,
                'provider_id' => $mission->provider_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'type' => 'provider_to_company',
            ]);

            // Notify company
            Notification::createForUser(
                $mission->company->user_id,
                'new_review',
                'New Review',
                "You received a {$request->rating}-star review for '{$mission->title}'",
                ['mission_id' => $mission->id, 'review_id' => $review->id]
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'review' => $review,
        ], 201);
    }

    public function providerReviews(Provider $provider)
    {
        $reviews = Review::where('provider_id', $provider->id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($reviews);
    }

    public function companyReviews(Company $company)
    {
        $reviews = Review::whereHas('mission', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })
            ->where('type', 'provider_to_company')
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($reviews);
    }

    private function updateProviderRating($providerId)
    {
        $provider = Provider::find($providerId);

        $avgRating = Review::where('provider_id', $providerId)
            ->where('type', 'company_to_provider')
            ->avg('rating');

        $totalReviews = Review::where('provider_id', $providerId)
            ->where('type', 'company_to_provider')
            ->count();

        $provider->update([
            'rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }
}
