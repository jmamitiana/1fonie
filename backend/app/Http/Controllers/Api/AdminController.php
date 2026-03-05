<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mission;
use App\Models\Payment;
use App\Models\Company;
use App\Models\Provider;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalCompanies = Company::count();
        $totalProviders = Provider::count();
        $totalMissions = Mission::count();
        $totalPayments = Payment::sum('amount');
        $platformFees = Payment::sum('platform_fee');

        $recentMissions = Mission::with(['company.user', 'provider.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_users' => $totalUsers,
                'total_companies' => $totalCompanies,
                'total_providers' => $totalProviders,
                'total_missions' => $totalMissions,
                'total_payments' => $totalPayments,
                'platform_fees' => $platformFees,
            ],
            'recent_missions' => $recentMissions,
            'recent_users' => $recentUsers,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::with(['company', 'provider']);

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($users);
    }

    public function toggleUserActive(Request $request, User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return response()->json([
            'user' => $user,
            'is_active' => $user->is_active,
        ]);
    }

    public function deleteUser(Request $request, User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function missions(Request $request)
    {
        $query = Mission::with(['company.user', 'provider.user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $missions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($missions);
    }

    public function updateMissionStatus(Request $request, Mission $mission)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Mission::STATUSES),
        ]);

        $mission->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'mission' => $mission->fresh(),
        ]);
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['mission', 'company.user', 'provider.user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($payments);
    }

    public function analytics(Request $request)
    {
        // Get analytics data
        $missionsByStatus = Mission::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $missionsByCategory = Mission::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        $paymentsByMonth = Payment::selectRaw('
            MONTH(created_at) as month,
            YEAR(created_at) as year,
            SUM(amount) as total,
            COUNT(*) as count
        ')
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $usersByMonth = User::selectRaw('
            MONTH(created_at) as month,
            YEAR(created_at) as year,
            COUNT(*) as count,
            role
        ')
            ->groupBy('month', 'year', 'role')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'missions_by_status' => $missionsByStatus,
            'missions_by_category' => $missionsByCategory,
            'payments_by_month' => $paymentsByMonth,
            'users_by_month' => $usersByMonth,
        ]);
    }

    public function disputes(Request $request)
    {
        $disputes = Mission::where('status', 'disputed')
            ->with(['company.user', 'provider.user', 'payment'])
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($disputes);
    }

    public function resolveDispute(Request $request, Mission $mission)
    {
        $request->validate([
            'resolution' => 'required|string',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        // Update mission status
        $mission->update([
            'status' => 'completed',
            'cancellation_reason' => $request->resolution,
        ]);

        // Handle payment refund if needed
        if ($mission->payment) {
            if ($request->refund_amount > 0) {
                $mission->payment->update([
                    'status' => 'refunded',
                ]);
            } else {
                $mission->payment->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);
            }
        }

        return response()->json([
            'mission' => $mission->fresh(),
        ]);
    }
}
