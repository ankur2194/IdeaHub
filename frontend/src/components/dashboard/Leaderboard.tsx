import React from 'react';
import { TrophyIcon } from '@heroicons/react/24/solid';
import Avatar from '../common/Avatar';
import type { LeaderboardEntry } from '../../services/analyticsService';

interface LeaderboardProps {
  entries: LeaderboardEntry[];
  loading?: boolean;
}

const Leaderboard: React.FC<LeaderboardProps> = ({ entries, loading }) => {
  if (loading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">
          🏆 Top Contributors
        </h3>
        <div className="space-y-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="animate-pulse flex items-center gap-3">
              <div className="w-8 h-8 bg-gray-200 rounded"></div>
              <div className="w-10 h-10 bg-gray-200 rounded-full"></div>
              <div className="flex-1">
                <div className="h-4 bg-gray-200 rounded w-32 mb-2"></div>
                <div className="h-3 bg-gray-200 rounded w-24"></div>
              </div>
              <div className="h-6 bg-gray-200 rounded w-16"></div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  const getRankBadge = (rank: number) => {
    const colors: Record<number, string> = {
      1: 'bg-yellow-100 text-yellow-800 border-yellow-300',
      2: 'bg-gray-100 text-gray-800 border-gray-300',
      3: 'bg-orange-100 text-orange-800 border-orange-300',
    };

    return colors[rank] || 'bg-blue-50 text-blue-700 border-blue-200';
  };

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <TrophyIcon className="h-6 w-6 text-yellow-500" />
        Top Contributors
      </h3>

      {entries.length === 0 ? (
        <p className="text-center text-gray-500 py-8">No contributors yet</p>
      ) : (
        <div className="space-y-3">
          {entries.map((entry) => (
            <div
              key={entry.id}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
            >
              {/* Rank Badge */}
              <div
                className={`flex items-center justify-center w-8 h-8 rounded-full border-2 font-bold text-sm ${getRankBadge(
                  entry.rank
                )}`}
              >
                {entry.rank}
              </div>

              {/* Avatar */}
              <Avatar name={entry.name} src={entry.avatar} size="md" />

              {/* User Info */}
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">
                  {entry.name}
                </p>
                <p className="text-xs text-gray-500 truncate">
                  {entry.department && `${entry.department}`}
                  {entry.job_title && ` • ${entry.job_title}`}
                </p>
              </div>

              {/* Points */}
              <div className="flex items-center gap-1">
                <span className="text-lg font-bold text-yellow-600">
                  {entry.points}
                </span>
                <span className="text-xs text-gray-500">pts</span>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default Leaderboard;
