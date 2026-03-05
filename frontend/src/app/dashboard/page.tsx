'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { companyAPI, providerAPI } from '@/services/api';
import { Mission, DashboardStats } from '@/types';

export default function DashboardPage() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const router = useRouter();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [missions, setMissions] = useState<Mission[]>([]);
  const [loadingData, setLoadingData] = useState(true);

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push('/login');
    }
  }, [isLoading, isAuthenticated, router]);

  useEffect(() => {
    if (user) {
      fetchDashboardData();
    }
  }, [user]);

  const fetchDashboardData = async () => {
    try {
      if (user?.role === 'company') {
        const response = await companyAPI.getDashboard();
        setStats(response.data.stats);
        setMissions(response.data.recent_missions);
      } else if (user?.role === 'provider') {
        const response = await providerAPI.getDashboard();
        setStats(response.data.stats);
        setMissions(response.data.recent_missions);
      }
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      setLoadingData(false);
    }
  };

  if (isLoading || loadingData) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!user) return null;

  const isCompany = user.role === 'company';
  const isProvider = user.role === 'provider';

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center">
              <Link href="/" className="flex items-center space-x-2">
                <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-lg flex items-center justify-center">
                  <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <span className="text-xl font-bold text-gray-900">TechConnect</span>
              </Link>
            </div>
            <nav className="flex items-center space-x-4">
              <Link href="/missions" className="text-gray-600 hover:text-gray-900">
                Browse Missions
              </Link>
              <div className="flex items-center space-x-2">
                <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <span className="text-blue-600 font-medium">{user.name.charAt(0)}</span>
                </div>
                <span className="text-gray-700">{user.name}</span>
              </div>
            </nav>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Welcome */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">
            {isCompany ? 'Company Dashboard' : isProvider ? 'Provider Dashboard' : 'Admin Dashboard'}
          </h1>
          <p className="text-gray-600 mt-2">
            {isCompany 
              ? 'Manage your missions and find the best service providers'
              : 'Find available missions and manage your applications'
            }
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {isCompany && (
            <>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Total Missions</div>
                <div className="text-3xl font-bold text-gray-900">{stats?.total_missions || 0}</div>
              </div>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Open Missions</div>
                <div className="text-3xl font-bold text-blue-600">{stats?.open_missions || 0}</div>
              </div>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">In Progress</div>
                <div className="text-3xl font-bold text-yellow-600">{stats?.in_progress_missions || 0}</div>
              </div>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Total Spent</div>
                <div className="text-3xl font-bold text-green-600">€{stats?.total_spent?.toFixed(2) || '0.00'}</div>
              </div>
            </>
          )}
          {isProvider && (
            <>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Available Missions</div>
                <div className="text-3xl font-bold text-blue-600">{stats?.available_missions || 0}</div>
              </div>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Assigned</div>
                <div className="text-3xl font-bold text-yellow-600">{stats?.assigned_missions || 0}</div>
              </div>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Completed</div>
                <div className="text-3xl font-bold text-green-600">{stats?.completed_missions || 0}</div>
              </div>
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="text-sm text-gray-500 mb-1">Total Earnings</div>
                <div className="text-3xl font-bold text-green-600">€{stats?.total_earnings?.toFixed(2) || '0.00'}</div>
              </div>
            </>
          )}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          {isCompany && (
            <div className="bg-white rounded-xl shadow-sm p-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
              <div className="space-y-3">
                <Link 
                  href="/missions/create"
                  className="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                >
                  <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                    <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                  <div>
                    <div className="font-medium text-gray-900">Create New Mission</div>
                    <div className="text-sm text-gray-500">Post a new technical intervention</div>
                  </div>
                </Link>
                <Link 
                  href="/missions"
                  className="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div className="w-10 h-10 bg-gray-600 rounded-lg flex items-center justify-center mr-3">
                    <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="font-medium text-gray-900">Browse Missions</div>
                    <div className="text-sm text-gray-500">View all available missions</div>
                  </div>
                </Link>
              </div>
            </div>
          )}
          {isProvider && (
            <div className="bg-white rounded-xl shadow-sm p-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
              <div className="space-y-3">
                <Link 
                  href="/missions/available"
                  className="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                >
                  <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                    <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="font-medium text-gray-900">Find Missions</div>
                    <div className="text-sm text-gray-500">Browse available missions</div>
                  </div>
                </Link>
                <Link 
                  href="/profile"
                  className="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div className="w-10 h-10 bg-gray-600 rounded-lg flex items-center justify-center mr-3">
                    <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div>
                    <div className="font-medium text-gray-900">Update Profile</div>
                    <div className="text-sm text-gray-500">Manage your provider profile</div>
                  </div>
                </Link>
              </div>
            </div>
          )}
        </div>

        {/* Recent Missions */}
        <div className="bg-white rounded-xl shadow-sm p-6">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-lg font-semibold text-gray-900">Recent Missions</h2>
            <Link 
              href={isCompany ? '/missions' : '/missions/available'}
              className="text-blue-600 hover:text-blue-700 font-medium"
            >
              View All
            </Link>
          </div>
          
          {missions.length > 0 ? (
            <div className="space-y-4">
              {missions.map((mission) => (
                <div key={mission.id} className="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                  <div className="flex-1">
                    <div className="font-medium text-gray-900">{mission.title}</div>
                    <div className="text-sm text-gray-500">
                      {mission.location_city} • €{mission.price}
                    </div>
                  </div>
                  <div className="flex items-center space-x-4">
                    <span className={`status-badge status-${mission.status}`}>
                      {mission.status.replace('_', ' ')}
                    </span>
                    <Link 
                      href={`/missions/${mission.id}`}
                      className="text-blue-600 hover:text-blue-700"
                    >
                      View
                    </Link>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8 text-gray-500">
              No missions yet. {isCompany ? 'Create your first mission!' : 'Browse available missions!'}
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
