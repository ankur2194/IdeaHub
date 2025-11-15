import React from 'react';
import { LockClosedIcon, CheckCircleIcon } from '@heroicons/react/24/solid';
import type { Badge } from '../../services/gamificationService';

interface BadgeCardProps {
  badge: Badge;
  earned?: boolean;
  earnedAt?: string | null;
  progress?: number;
  showProgress?: boolean;
}

const BadgeCard: React.FC<BadgeCardProps> = ({
  badge,
  earned = false,
  earnedAt,
  progress = 0,
  showProgress = false,
}) => {
  const getRarityColor = (rarity: string) => {
    switch (rarity) {
      case 'legendary':
        return 'from-yellow-400 via-orange-500 to-red-500';
      case 'epic':
        return 'from-purple-400 via-pink-500 to-red-500';
      case 'rare':
        return 'from-blue-400 to-blue-600';
      default:
        return 'from-gray-400 to-gray-600';
    }
  };

  const getRarityBorder = (rarity: string) => {
    switch (rarity) {
      case 'legendary':
        return 'border-yellow-400 shadow-yellow-500/50';
      case 'epic':
        return 'border-purple-400 shadow-purple-500/50';
      case 'rare':
        return 'border-blue-400 shadow-blue-500/50';
      default:
        return 'border-gray-300';
    }
  };

  const getRarityText = (rarity: string) => {
    switch (rarity) {
      case 'legendary':
        return 'text-yellow-600';
      case 'epic':
        return 'text-purple-600';
      case 'rare':
        return 'text-blue-600';
      default:
        return 'text-gray-600';
    }
  };

  return (
    <div
      className={`group relative overflow-hidden rounded-lg border-2 bg-white p-4 transition-all duration-300 ${
        earned
          ? `${getRarityBorder(badge.rarity)} shadow-lg hover:shadow-xl`
          : 'border-gray-200 opacity-60 hover:opacity-80'
      }`}
    >
      {/* Rarity Gradient Background */}
      {earned && (
        <div
          className={`absolute inset-0 bg-gradient-to-br ${getRarityColor(badge.rarity)} opacity-5`}
        ></div>
      )}

      <div className="relative space-y-3">
        {/* Badge Icon */}
        <div className="flex items-center justify-center">
          <div
            className={`flex h-20 w-20 items-center justify-center rounded-full text-5xl ${
              earned ? 'scale-100' : 'scale-90 grayscale'
            } transition-transform duration-300 group-hover:scale-105`}
          >
            {earned ? (
              badge.icon
            ) : (
              <div className="relative">
                <div className="absolute inset-0 flex items-center justify-center">
                  <LockClosedIcon className="h-8 w-8 text-gray-400" />
                </div>
                <div className="opacity-30">{badge.icon}</div>
              </div>
            )}
          </div>
        </div>

        {/* Badge Info */}
        <div className="text-center">
          <h3
            className={`font-semibold ${earned ? 'text-gray-900' : 'text-gray-500'}`}
          >
            {badge.name}
          </h3>
          <p className="mt-1 text-xs text-gray-600 line-clamp-2">{badge.description}</p>

          {/* Rarity Badge */}
          <div className="mt-2 flex items-center justify-center gap-2">
            <span
              className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium uppercase ${getRarityText(badge.rarity)}`}
            >
              {badge.rarity}
            </span>
            {badge.points_reward > 0 && (
              <span className="inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                +{badge.points_reward} pts
              </span>
            )}
          </div>
        </div>

        {/* Earned Status */}
        {earned && earnedAt && (
          <div className="flex items-center justify-center gap-1 text-xs text-green-600">
            <CheckCircleIcon className="h-4 w-4" />
            <span>
              Earned {new Date(earnedAt).toLocaleDateString()}
            </span>
          </div>
        )}

        {/* Progress Bar (if not earned) */}
        {!earned && showProgress && progress > 0 && (
          <div className="space-y-1">
            <div className="flex justify-between text-xs text-gray-600">
              <span>Progress</span>
              <span>{Math.round(progress)}%</span>
            </div>
            <div className="h-2 w-full overflow-hidden rounded-full bg-gray-200">
              <div
                className="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500"
                style={{ width: `${progress}%` }}
              ></div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default BadgeCard;
