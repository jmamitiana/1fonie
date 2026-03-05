<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Mission;
use App\Models\Application;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    public function profile(Request $request)
    {
        $provider = $request->user()->provider;

        return response()->json([
            'provider' => $provider->load('user'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $provider = $request->user()->provider;

        $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'specialty' => 'nullable|string|max:100',
            'service_categories' => 'nullable|array',
            'service_areas' => 'nullable|array',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $provider->update($request->all());

        return response()->json([
            'provider' => $provider->fresh()->load('user'),
        ]);
    }

    public function connectStripe(Request $request)
    {
        $provider = $request->user()->provider;

        // In a real implementation, this would create a Stripe Connect account
        // For now, we'll just store a placeholder
        $provider->update([
            'stripe_account_id' => 'acct_' . uniqid(),
        ]);

        return response()->json([
            'provider' => $provider,
            'message' => 'Stripe Connect account linked successfully',
        ]);
    }

    public function toggleAvailability(Request $request)
    {
        $provider = $request->user()->provider;

        $provider->update([
            'is_available' => !$provider->is_available,
        ]);

        return response()->json([
            'provider' => $provider,
            'is_available' => $provider->is_available,
        ]);
    }

    public function missions(Request $request)
    {
        $provider = $request->user()->provider;

        $missions = $provider->missions()
            ->with('company.user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($missions);
    }

    public function availableMissions(Request $request)
    {
        $provider = $request->user()->provider;

        $query = Mission::with(['company.user'])
            ->where('status', 'open');

        // Filter by service categories if provider has them
        if ($provider->service_categories && count($provider->service_categories) > 0) {
            $query->whereIn('category', $provider->service_categories);
        }

        // Filter by service areas if provider has them
        if ($provider->service_areas && count($provider->service_areas) > 0) {
            $query->whereIn('location_city', $provider->service_areas);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('city')) {
            $query->where('location_city', 'like', "%{$request->city}%");
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $missions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Filter out missions the provider has already applied to
        $appliedMissionIds = Application::where('provider_id', $provider->id)
            ->pluck('mission_id');

        $missions->getCollection()->transform(function ($mission) use ($appliedMissionIds) {
            $mission->has_applied = $appliedMissionIds->contains($mission->id);
            return $mission;
        });

        return response()->json($missions);
    }

    public function showMission(Request $request, Mission $mission)
    {
        $provider = $request->user()->provider;

        $application = $mission->applications()
            ->where('provider_id', $provider->id)
            ->first();

        $mission->load(['company.user', 'applications.provider.user']);

        return response()->json([
            'mission' => $mission,
            'application' => $application,
        ]);
    }

    public function applyMission(Request $request, Mission $mission)
    {
        $provider = $request->user()->provider;

        // Check if already applied
        $existingApplication = $mission->applications()
            ->where('provider_id', $provider->id)
            ->first();

        if ($existingApplication) {
            return response()->json(['message' => 'Already applied to this mission'], 400);
        }

        if ($mission->status !== 'open') {
            return response()->json(['message' => 'Mission is not open for applications'], 400);
        }

        $request->validate([
            'cover_letter' => 'nullable|string',
            'proposed_price' => 'nullable|numeric|min:0',
            'proposed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $application = Application::create([
            'mission_id' => $mission->id,
            'provider_id' => $provider->id,
            'cover_letter' => $request->cover_letter,
            'proposed_price' => $request->proposed_price,
            'proposed_date' => $request->proposed_date,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        // Notify company
        Notification::createForUser(
            $mission->company->user_id,
            'new_application',
            'New Application',
            "A provider has applied for your mission '{$mission->title}'",
            ['mission_id' => $mission->id, 'application_id' => $application->id]
        );

        return response()->json([
            'application' => $application->load('provider'),
        ], 201);
    }

    public function withdrawApplication(Request $request, Mission $mission)
    {
        $provider = $request->user()->provider;

        $application = $mission->applications()
            ->where('provider_id', $provider->id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'No application found'], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Cannot withdraw application in current status'], 400);
        }

        $application->update(['status' => 'withdrawn']);

        return response()->json(['message' => 'Application withdrawn successfully']);
    }

    public function applications(Request $request)
    {
        $provider = $request->user()->provider;

        $applications = $provider->applications()
            ->with('mission.company.user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($applications);
    }

    public function earnings(Request $request)
    {
        $provider = $request->user()->provider;

        $earnings = Payment::where('provider_id', $provider->id)
            ->where('status', 'released')
            ->selectRaw('
                SUM(provider_amount) as total_earnings,
                COUNT(*) as total_missions,
                MONTH(released_at) as month,
                YEAR(released_at) as year
            ')
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $totalEarnings = $provider->payments()
            ->where('status', 'released')
            ->sum('provider_amount');

        return response()->json([
            'total_earnings' => $totalEarnings,
            'earnings_by_month' => $earnings,
        ]);
    }

    public function dashboard(Request $request)
    {
        $provider = $request->user()->provider;

        $availableMissions = Mission::where('status', 'open')
            ->when($provider->service_categories, function ($query) use ($provider) {
                if (count($provider->service_categories) > 0) {
                    $query->whereIn('category', $provider->service_categories);
                }
            })
            ->count();

        $assignedMissions = $provider->missions()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        $completedMissions = $provider->missions()
            ->where('status', 'completed')
            ->count();

        $totalEarnings = $provider->payments()
            ->where('status', 'released')
            ->sum('provider_amount');

        $recentMissions = $provider->missions()
            ->with('company.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'available_missions' => $availableMissions,
                'assigned_missions' => $assignedMissions,
                'completed_missions' => $completedMissions,
                'total_earnings' => $totalEarnings,
            ],
            'recent_missions' => $recentMissions,
        ]);
    }
}
