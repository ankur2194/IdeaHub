import api from './api';
import type { ApiResponse } from '../types';

export interface OverviewStats {
  total_ideas: number;
  total_users: number;
  total_comments: number;
  pending_ideas: number;
  approved_ideas: number;
  implemented_ideas: number;
  this_month_ideas: number;
  ideas_growth_percentage: number;
}

export interface IdeasTrendData {
  dates: string[];
  series: Record<string, Record<string, number>>;
}

export interface CategoryDistribution {
  name: string;
  value: number;
  color: string;
}

export interface StatusBreakdown {
  status: string;
  count: number;
  label: string;
}

export interface LeaderboardEntry {
  rank: number;
  id: number;
  name: string;
  avatar: string | null;
  department: string | null;
  job_title: string | null;
  points: number;
}

export interface DepartmentStats {
  department: string;
  ideas_count: number;
}

export interface RecentActivity {
  type: string;
  id: number;
  title: string;
  user: {
    name: string;
    avatar: string | null;
  };
  category: string | null;
  status: string;
  created_at: string;
  timestamp: string;
}

export interface UserStats {
  total_ideas: number;
  total_comments: number;
  total_points: number;
  approved_ideas: number;
  implemented_ideas: number;
  rank: number;
}

export const analyticsService = {
  /**
   * Get dashboard overview statistics
   */
  getOverview: async (): Promise<ApiResponse<OverviewStats>> => {
    const response = await api.get('/analytics/overview');
    return response.data;
  },

  /**
   * Get ideas trend over time
   */
  getIdeasTrend: async (period: string = '30days'): Promise<ApiResponse<IdeasTrendData>> => {
    const response = await api.get('/analytics/ideas-trend', {
      params: { period },
    });
    return response.data;
  },

  /**
   * Get category distribution
   */
  getCategoryDistribution: async (): Promise<ApiResponse<CategoryDistribution[]>> => {
    const response = await api.get('/analytics/category-distribution');
    return response.data;
  },

  /**
   * Get status breakdown
   */
  getStatusBreakdown: async (): Promise<ApiResponse<StatusBreakdown[]>> => {
    const response = await api.get('/analytics/status-breakdown');
    return response.data;
  },

  /**
   * Get leaderboard
   */
  getLeaderboard: async (limit: number = 10): Promise<ApiResponse<LeaderboardEntry[]>> => {
    const response = await api.get('/analytics/leaderboard', {
      params: { limit },
    });
    return response.data;
  },

  /**
   * Get department statistics
   */
  getDepartmentStats: async (): Promise<ApiResponse<DepartmentStats[]>> => {
    const response = await api.get('/analytics/department-stats');
    return response.data;
  },

  /**
   * Get recent activity
   */
  getRecentActivity: async (limit: number = 10): Promise<ApiResponse<RecentActivity[]>> => {
    const response = await api.get('/analytics/recent-activity', {
      params: { limit },
    });
    return response.data;
  },

  /**
   * Get user statistics
   */
  getUserStats: async (): Promise<ApiResponse<UserStats>> => {
    const response = await api.get('/analytics/user-stats');
    return response.data;
  },
};
