import { useEffect, useState } from 'react';
import {
  LightBulbIcon,
  UsersIcon,
  ChatBubbleLeftIcon,
  ClockIcon,
  CheckCircleIcon,
  RocketLaunchIcon,
} from '@heroicons/react/24/outline';
import StatCard from '../components/dashboard/StatCard';
import Leaderboard from '../components/dashboard/Leaderboard';
import RecentActivity from '../components/dashboard/RecentActivity';
import { analyticsService } from '../services/analyticsService';
import type {
  OverviewStats,
  LeaderboardEntry,
  RecentActivity as RecentActivityType,
  CategoryDistribution,
  StatusBreakdown,
} from '../services/analyticsService';

const Analytics = () => {
  const [overview, setOverview] = useState<OverviewStats | null>(null);
  const [leaderboard, setLeaderboard] = useState<LeaderboardEntry[]>([]);
  const [recentActivity, setRecentActivity] = useState<RecentActivityType[]>([]);
  const [categoryData, setCategoryData] = useState<CategoryDistribution[]>([]);
  const [statusData, setStatusData] = useState<StatusBreakdown[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadAnalytics();
  }, []);

  const loadAnalytics = async () => {
    setLoading(true);
    try {
      const [
        overviewRes,
        leaderboardRes,
        activityRes,
        categoryRes,
        statusRes,
      ] = await Promise.all([
        analyticsService.getOverview(),
        analyticsService.getLeaderboard(10),
        analyticsService.getRecentActivity(10),
        analyticsService.getCategoryDistribution(),
        analyticsService.getStatusBreakdown(),
      ]);

      setOverview(overviewRes.data);
      setLeaderboard(leaderboardRes.data);
      setRecentActivity(activityRes.data);
      setCategoryData(categoryRes.data);
      setStatusData(statusRes.data);
    } catch (error) {
      console.error('Failed to load analytics:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading || !overview) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-48 mb-8"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {[1, 2, 3, 4].map((i) => (
              <div key={i} className="h-32 bg-gray-200 rounded-lg"></div>
            ))}
          </div>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="h-96 bg-gray-200 rounded-lg"></div>
            <div className="h-96 bg-gray-200 rounded-lg"></div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Page Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p className="mt-2 text-gray-600">
          Track innovation metrics and team performance
        </p>
      </div>

      {/* Overview Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          title="Total Ideas"
          value={overview.total_ideas}
          icon={<LightBulbIcon className="h-6 w-6 text-white" />}
          trend={overview.ideas_growth_percentage}
          trendLabel="this month"
          color="purple"
        />
        <StatCard
          title="Active Users"
          value={overview.total_users}
          icon={<UsersIcon className="h-6 w-6 text-white" />}
          color="blue"
        />
        <StatCard
          title="Comments"
          value={overview.total_comments}
          icon={<ChatBubbleLeftIcon className="h-6 w-6 text-white" />}
          color="green"
        />
        <StatCard
          title="Pending Reviews"
          value={overview.pending_ideas}
          icon={<ClockIcon className="h-6 w-6 text-white" />}
          color="orange"
        />
      </div>

      {/* Secondary Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <StatCard
          title="Approved Ideas"
          value={overview.approved_ideas}
          icon={<CheckCircleIcon className="h-6 w-6 text-white" />}
          color="green"
        />
        <StatCard
          title="Implemented"
          value={overview.implemented_ideas}
          icon={<RocketLaunchIcon className="h-6 w-6 text-white" />}
          color="blue"
        />
        <StatCard
          title="This Month"
          value={overview.this_month_ideas}
          icon={<LightBulbIcon className="h-6 w-6 text-white" />}
          trend={overview.ideas_growth_percentage}
          color="purple"
        />
      </div>

      {/* Charts and Visualizations */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {/* Category Distribution */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">
            Ideas by Category
          </h3>
          <div className="space-y-3">
            {categoryData.length === 0 ? (
              <p className="text-center text-gray-500 py-8">No data available</p>
            ) : (
              categoryData.map((category) => {
                const percentage = overview.total_ideas > 0
                  ? Math.round((category.value / overview.total_ideas) * 100)
                  : 0;

                return (
                  <div key={category.name} className="space-y-2">
                    <div className="flex items-center justify-between">
                      <span className="text-sm font-medium text-gray-700">
                        {category.name}
                      </span>
                      <span className="text-sm text-gray-500">
                        {category.value} ({percentage}%)
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div
                        className="h-2 rounded-full transition-all duration-300"
                        style={{
                          width: `${percentage}%`,
                          backgroundColor: category.color,
                        }}
                      ></div>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>

        {/* Status Breakdown */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">
            Ideas by Status
          </h3>
          <div className="space-y-3">
            {statusData.length === 0 ? (
              <p className="text-center text-gray-500 py-8">No data available</p>
            ) : (
              statusData.map((status) => {
                const percentage = overview.total_ideas > 0
                  ? Math.round((status.count / overview.total_ideas) * 100)
                  : 0;

                const statusColors: Record<string, string> = {
                  draft: '#6b7280',
                  pending: '#f59e0b',
                  approved: '#10b981',
                  rejected: '#ef4444',
                  implemented: '#3b82f6',
                };

                return (
                  <div key={status.status} className="space-y-2">
                    <div className="flex items-center justify-between">
                      <span className="text-sm font-medium text-gray-700">
                        {status.label}
                      </span>
                      <span className="text-sm text-gray-500">
                        {status.count} ({percentage}%)
                      </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div
                        className="h-2 rounded-full transition-all duration-300"
                        style={{
                          width: `${percentage}%`,
                          backgroundColor: statusColors[status.status] || '#6b7280',
                        }}
                      ></div>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>
      </div>

      {/* Leaderboard and Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Leaderboard entries={leaderboard} loading={false} />
        <RecentActivity activities={recentActivity} loading={false} />
      </div>
    </div>
  );
};

export default Analytics;
