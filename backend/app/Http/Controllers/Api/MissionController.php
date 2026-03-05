<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Mission::with(['company.user', 'provider.user'])
            ->where('status', 'open');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('city')) {
            $query->where('location_city', 'like', "%{$request->city}%");
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('date')) {
            $query->where('intervention_date', $request->date);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $missions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($missions);
    }

    public function show(Mission $mission)
    {
        $mission->load(['company.user', 'provider.user', 'applications.provider.user']);

        return response()->json([
            'mission' => $mission,
        ]);
    }

    public function categories()
    {
        return response()->json([
            'categories' => [
                ['value' => 'it_support', 'label' => 'IT Support'],
                ['value' => 'plumbing', 'label' => 'Plumbing'],
                ['value' => 'electrical', 'label' => 'Electrical'],
                ['value' => 'network_installation', 'label' => 'Network Installation'],
                ['value' => 'hvac', 'label' => 'HVAC'],
                ['value' => 'security', 'label' => 'Security'],
                ['value' => 'maintenance', 'label' => 'Maintenance'],
                ['value' => 'construction', 'label' => 'Construction'],
                ['value' => 'other', 'label' => 'Other'],
            ],
        ]);
    }

    public function cities()
    {
        $cities = Mission::distinct()
            ->whereNotNull('location_city')
            ->pluck('location_city')
            ->filter()
            ->values();

        return response()->json([
            'cities' => $cities,
        ]);
    }
}
