<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Mission;
use App\Models\Application;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function profile(Request $request)
    {
        $company = $request->user()->company;

        return response()->json([
            'company' => $company->load('user'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $company = $request->user()->company;

        $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:100',
            'company_country' => 'nullable|string|max:100',
            'company_zipcode' => 'nullable|string|max:20',
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'nullable|string|max:255',
            'company_tax_id' => 'nullable|string|max:50',
        ]);

        $company->update($request->all());

        return response()->json([
            'company' => $company->fresh()->load('user'),
        ]);
    }

    public function missions(Request $request)
    {
        $company = $request->user()->company;

        $missions = $company->missions()
            ->with(['provider.user', 'applications'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($missions);
    }

    public function createMission(Request $request)
    {
        $company = $request->user()->company;

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:' . implode(',', Mission::CATEGORIES),
            'location_city' => 'required|string|max:100',
            'location_address' => 'nullable|string|max:255',
            'location_country' => 'nullable|string|max:100',
            'location_zipcode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'intervention_date' => 'required|date|after_or_equal:today',
            'intervention_time' => 'nullable',
            'price' => 'required|numeric|min:0',
            'attachments' => 'nullable|array',
        ]);

        $platformFee = $request->price * 0.20; // 20% platform fee

        $mission = $company->missions()->create([
            ...$request->all(),
            'platform_fee' => $platformFee,
            'status' => 'open',
        ]);

        return response()->json([
            'mission' => $mission->load('company'),
        ], 201);
    }

    public function showMission(Request $request, Mission $mission)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        $mission->load(['company.user', 'provider.user', 'applications.provider.user', 'payment']);

        return response()->json([
            'mission' => $mission,
        ]);
    }

    public function updateMission(Request $request, Mission $mission)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        if (!in_array($mission->status, ['draft', 'open'])) {
            return response()->json(['message' => 'Cannot update mission in current status'], 400);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|in:' . implode(',', Mission::CATEGORIES),
            'location_city' => 'sometimes|string|max:100',
            'location_address' => 'nullable|string|max:255',
            'location_country' => 'nullable|string|max:100',
            'location_zipcode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'intervention_date' => 'sometimes|date|after_or_equal:today',
            'intervention_time' => 'nullable',
            'price' => 'sometimes|numeric|min:0',
            'attachments' => 'nullable|array',
        ]);

        if ($request->has('price') && $request->price !== $mission->price) {
            $platformFee = $request->price * 0.20;
            $mission->update([
                ...$request->all(),
                'platform_fee' => $platformFee,
            ]);
        } else {
            $mission->update($request->all());
        }

        return response()->json([
            'mission' => $mission->fresh()->load('company'),
        ]);
    }

    public function deleteMission(Request $request, Mission $mission)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        if (!in_array($mission->status, ['draft', 'cancelled'])) {
            return response()->json(['message' => 'Cannot delete mission in current status'], 400);
        }

        $mission->delete();

        return response()->json(['message' => 'Mission deleted successfully']);
    }

    public function missionApplications(Request $request, Mission $mission)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        $applications = $mission->applications()
            ->with('provider.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'applications' => $applications,
        ]);
    }

    public function selectProvider(Request $request, Mission $mission, Application $application)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        if ($mission->status !== 'open') {
            return response()->json(['message' => 'Mission is not open for selection'], 400);
        }

        if ($application->mission_id !== $mission->id) {
            return response()->json(['message' => 'Application does not belong to this mission'], 400);
        }

        // Reject all other applications
        $mission->applications()
            ->where('id', '!=', $application->id)
            ->update(['status' => 'rejected']);

        // Accept the selected application
        $application->update([
            'status' => 'accepted',
            'reviewed_at' => now(),
        ]);

        // Update mission
        $mission->update([
            'provider_id' => $application->provider_id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        // Notify provider
        Notification::createForUser(
            $application->provider->user_id,
            'application_accepted',
            'Application Accepted',
            "Your application for '{$mission->title}' has been accepted!",
            ['mission_id' => $mission->id]
        );

        return response()->json([
            'mission' => $mission->fresh()->load('provider.user', 'applications'),
        ]);
    }

    public function payMission(Request $request, Mission $mission)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        if ($mission->status !== 'assigned') {
            return response()->json(['message' => 'Mission must be assigned before payment'], 400);
        }

        if ($mission->payment) {
            return response()->json(['message' => 'Mission already has a payment'], 400);
        }

        $payment = Payment::create([
            'mission_id' => $mission->id,
            'company_id' => $mission->company_id,
            'provider_id' => $mission->provider_id,
            'amount' => $mission->total_amount,
            'platform_fee' => $mission->platform_fee,
            'provider_amount' => $mission->price,
            'status' => 'held',
            'currency' => 'EUR',
            'description' => "Payment for mission: {$mission->title}",
        ]);

        $mission->update(['status' => 'in_progress']);

        return response()->json([
            'payment' => $payment,
            'mission' => $mission->fresh(),
        ]);
    }

    public function completeMission(Request $request, Mission $mission)
    {
        $this->authorizeCompanyMission($request->user(), $mission);

        if ($mission->status !== 'in_progress') {
            return response()->json(['message' => 'Mission is not in progress'], 400);
        }

        $mission->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($mission->payment) {
            $mission->payment->update([
                'status' => 'released',
                'released_at' => now(),
            ]);
        }

        // Notify provider
        Notification::createForUser(
            $mission->provider->user_id,
            'mission_completed',
            'Mission Completed',
            "The mission '{$mission->title}' has been marked as completed.",
            ['mission_id' => $mission->id]
        );

        return response()->json([
            'mission' => $mission->fresh(),
        ]);
    }

    public function payments(Request $request)
    {
        $company = $request->user()->company;

        $payments = $company->payments()
            ->with(['mission', 'provider.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($payments);
    }

    public function dashboard(Request $request)
    {
        $company = $request->user()->company;

        $totalMissions = $company->missions()->count();
        $openMissions = $company->missions()->where('status', 'open')->count();
        $inProgressMissions = $company->missions()->where('status', 'in_progress')->count();
        $completedMissions = $company->missions()->where('status', 'completed')->count();

        $totalSpent = $company->payments()
            ->where('status', 'released')
            ->sum('amount');

        $recentMissions = $company->missions()
            ->with('provider.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_missions' => $totalMissions,
                'open_missions' => $openMissions,
                'in_progress_missions' => $inProgressMissions,
                'completed_missions' => $completedMissions,
                'total_spent' => $totalSpent,
            ],
            'recent_missions' => $recentMissions,
        ]);
    }

    private function authorizeCompanyMission($user, $mission)
    {
        if ($mission->company_id !== $user->company->id) {
            abort(403, 'Unauthorized');
        }
    }
}
