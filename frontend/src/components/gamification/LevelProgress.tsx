import React from 'react';
import { SparklesIcon } from '@heroicons/react/24/solid';

interface LevelProgressProps {
  level: number;
  experience: number;
  nextLevelXP: number;
  rank: string;
  showDetails?: boolean;
}

const LevelProgress: React.FC<LevelProgressProps> = ({
  level,
  experience,
  nextLevelXP,
  rank,
  showDetails = true,
}) => {
  const progress = nextLevelXP > 0 ? Math.min(100, (experience / nextLevelXP) * 100) : 0;
  const remainingXP = nextLevelXP - experience;

  // Get color based on level
  const getLevelColor = (lvl: number) => {
    if (lvl >= 50) return 'from-purple-500 to-pink-500';
    if (lvl >= 40) return 'from-blue-500 to-purple-500';
    if (lvl >= 30) return 'from-green-500 to-blue-500';
    if (lvl >= 20) return 'from-yellow-500 to-green-500';
    if (lvl >= 10) return 'from-orange-500 to-yellow-500';
    return 'from-gray-500 to-gray-600';
  };

  return (
    <div className="space-y-3">
      {/* Level Badge */}
      <div className="flex items-center gap-3">
        <div
          className={`flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br ${getLevelColor(level)} shadow-lg`}
        >
          <div className="text-center">
            <div className="text-2xl font-bold text-white">{level}</div>
            <div className="text-[10px] font-medium uppercase tracking-wide text-white/90">
              Level
            </div>
          </div>
        </div>

        <div className="flex-1">
          <div className="flex items-center gap-2">
            <h3 className="text-lg font-semibold text-gray-900">{rank}</h3>
            <SparklesIcon className="h-5 w-5 text-yellow-500" />
          </div>
          {showDetails && (
            <p className="text-sm text-gray-600">
              {experience.toLocaleString()} / {nextLevelXP.toLocaleString()} XP
            </p>
          )}
        </div>
      </div>

      {/* Progress Bar */}
      <div className="space-y-1">
        <div className="flex items-center justify-between text-xs text-gray-600">
          <span>Progress to Level {level + 1}</span>
          <span className="font-medium">{Math.round(progress)}%</span>
        </div>
        <div className="h-3 w-full overflow-hidden rounded-full bg-gray-200">
          <div
            className={`h-full rounded-full bg-gradient-to-r ${getLevelColor(level)} transition-all duration-500`}
            style={{ width: `${progress}%` }}
          >
            <div className="h-full w-full animate-pulse bg-white/20"></div>
          </div>
        </div>
        {showDetails && (
          <p className="text-xs text-gray-500">
            {remainingXP.toLocaleString()} XP needed for next level
          </p>
        )}
      </div>
    </div>
  );
};

export default LevelProgress;
