import api from './api';

export interface Badge {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon: string;
  type: string;
  category: string | null;
  criteria: Record<string, any>;
  points_reward: number;
  rarity: 'common' | 'rare' | 'epic' | 'legendary';
  order: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  pivot?: {
    earned_at: string;
    progress: number;
  };
}

export interface GamificationStats {
  level: number;
  experience: number;
  next_level_xp: number;
  level_progress: number;
  rank: string;
  points: number;
  total_badges: number;
  ideas_submitted: number;
  ideas_approved: number;
  comments_posted: number;
  likes_given: number;
  likes_received: number;
  badges: Badge[];
}

export interface BadgeProgress {
  badge: Badge;
  earned: boolean;
  earned_at: string | null;
  progress: Array<{
    metric: string;
    current: number;
    required: number;
    percentage: number;
    completed: boolean;
  }>;
}

export interface LeaderboardEntry {
  id: number;
  name: string;
  avatar: string | null;
  department: string | null;
  level: number;
  experience: number;
  title: string | null;
  points: number;
  total_badges: number;
}

export interface LevelRanking {
  level: number;
  title: string;
  min_level: number;
}

export interface RecentAchievement {
  user_id: number;
  user_name: string;
  user_avatar: string | null;
  badge_id: number;
  badge_name: string;
  badge_icon: string;
  badge_rarity: string;
  earned_at: string;
}

export interface XPBreakdownItem {
  action: string;
  xp: number;
  count: number;
  total_xp: number;
}

export interface XPBreakdown {
  breakdown: XPBreakdownItem[];
  total_xp_earned: number;
  current_level: number;
  current_xp: number;
  next_level_xp: number;
}

export const gamificationService = {
  // Get current user's gamification stats
  getMyStats: async () => {
    const response = await api.get<{ success: boolean; data: GamificationStats }>(
      '/gamification/my-stats'
    );
    return response.data;
  },

  // Get specific user's gamification stats
  getUserStats: async (userId: number) => {
    const response = await api.get<{ success: boolean; data: GamificationStats }>(
      `/gamification/user/${userId}`
    );
    return response.data;
  },

  // Get leaderboard
  getLeaderboard: async (limit = 20, offset = 0) => {
    const response = await api.get<{ success: boolean; data: LeaderboardEntry[] }>(
      '/gamification/leaderboard',
      { params: { limit, offset } }
    );
    return response.data;
  },

  // Get level rankings
  getLevelRankings: async () => {
    const response = await api.get<{ success: boolean; data: LevelRanking[] }>(
      '/gamification/level-rankings'
    );
    return response.data;
  },

  // Get recent achievements
  getRecentAchievements: async (limit = 10) => {
    const response = await api.get<{ success: boolean; data: RecentAchievement[] }>(
      '/gamification/recent-achievements',
      { params: { limit } }
    );
    return response.data;
  },

  // Get XP breakdown
  getXPBreakdown: async () => {
    const response = await api.get<{ success: boolean; data: XPBreakdown }>(
      '/gamification/xp-breakdown'
    );
    return response.data;
  },

  // Badge endpoints
  getAllBadges: async (filters?: { type?: string; category?: string; rarity?: string }) => {
    const response = await api.get<{ success: boolean; data: Badge[] }>('/badges', {
      params: filters,
    });
    return response.data;
  },

  getBadge: async (badgeId: number) => {
    const response = await api.get<{ success: boolean; data: Badge }>(`/badges/${badgeId}`);
    return response.data;
  },

  getMyBadges: async () => {
    const response = await api.get<{ success: boolean; data: Badge[] }>('/my/badges');
    return response.data;
  },

  getUserBadges: async (userId: number) => {
    const response = await api.get<{ success: boolean; data: Badge[] }>(`/badges/user/${userId}`);
    return response.data;
  },

  getBadgeProgress: async () => {
    const response = await api.get<{ success: boolean; data: BadgeProgress[] }>(
      '/my/badge-progress'
    );
    return response.data;
  },
};
