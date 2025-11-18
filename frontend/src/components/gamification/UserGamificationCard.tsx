import React, { useEffect, useState, useCallback } from 'react';
import {
  TrophyIcon,
  FireIcon,
  ChatBubbleLeftIcon,
  HeartIcon,
  LightBulbIcon,
  CheckCircleIcon,
} from '@heroicons/react/24/outline';
import LevelProgress from './LevelProgress';
import { gamificationService, type GamificationStats } from '../../services/gamificationService';

interface UserGamificationCardProps {
  userId?: number;
}

const UserGamificationCard: React.FC<UserGamificationCardProps> = ({ userId }) => {
  const [stats, setStats] = useState<GamificationStats | null>(null);
  const [loading, setLoading] = useState(true);

  const loadStats = useCallback(async () => {
    try {
      const response = userId
        ? await gamificationService.getUserStats(userId)
        : await gamificationService.getMyStats();
      setStats(response.data);
    } catch (error) {
      console.error('Failed to load gamification stats:', error);
    } finally {
      setLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    loadStats();
  }, [loadStats]);

  if (loading) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div className="h-40 animate-pulse rounded bg-gray-200"></div>
      </div>
    );
  }

  if (!stats) {
    return null;
  }

  const statCards = [
    {
      icon: TrophyIcon,
      label: 'Points',
      value: stats.points.toLocaleString(),
      color: 'text-yellow-600 bg-yellow-50',
    },
    {
      icon: FireIcon,
      label: 'Badges',
      value: stats.total_badges,
      color: 'text-orange-600 bg-orange-50',
    },
    {
      icon: LightBulbIcon,
      label: 'Ideas',
      value: stats.ideas_submitted,
      color: 'text-blue-600 bg-blue-50',
    },
    {
      icon: CheckCircleIcon,
      label: 'Approved',
      value: stats.ideas_approved,
      color: 'text-green-600 bg-green-50',
    },
    {
      icon: ChatBubbleLeftIcon,
      label: 'Comments',
      value: stats.comments_posted,
      color: 'text-purple-600 bg-purple-50',
    },
    {
      icon: HeartIcon,
      label: 'Likes',
      value: stats.likes_received,
      color: 'text-pink-600 bg-pink-50',
    },
  ];

  // Get top 3 recent badges
  const recentBadges = stats.badges.slice(0, 3);

  return (
    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <div className="space-y-6">
        {/* Level Progress */}
        <LevelProgress
          level={stats.level}
          experience={stats.experience}
          nextLevelXP={stats.next_level_xp}
          rank={stats.rank}
        />

        {/* Stats Grid */}
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
          {statCards.map((stat) => (
            <div
              key={stat.label}
              className="flex items-center gap-3 rounded-lg bg-gray-50 p-3"
            >
              <div className={`rounded-lg p-2 ${stat.color}`}>
                <stat.icon className="h-5 w-5" />
              </div>
              <div>
                <div className="text-lg font-bold text-gray-900">{stat.value}</div>
                <div className="text-xs text-gray-600">{stat.label}</div>
              </div>
            </div>
          ))}
        </div>

        {/* Recent Badges */}
        {recentBadges.length > 0 && (
          <div>
            <h3 className="mb-3 text-sm font-semibold text-gray-700">Recent Badges</h3>
            <div className="flex gap-2">
              {recentBadges.map((badge) => (
                <div
                  key={badge.id}
                  className="group relative flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-lg border-2 border-gray-200 bg-white text-2xl transition-all hover:scale-110 hover:border-blue-400 hover:shadow-lg"
                  title={badge.name}
                >
                  {badge.icon}
                  {/* Tooltip */}
                  <div className="pointer-events-none absolute -top-12 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                    {badge.name}
                  </div>
                </div>
              ))}
              {stats.total_badges > 3 && (
                <div className="flex h-14 w-14 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 text-sm font-medium text-gray-500">
                  +{stats.total_badges - 3}
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default UserGamificationCard;
