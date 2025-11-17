import React from 'react';
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/react/24/solid';
import type { StatsData, WidgetConfig } from '../../types/dashboard';

interface StatsCardProps {
  data: StatsData;
  config: WidgetConfig;
}

export const StatsCard: React.FC<StatsCardProps> = ({ data, config }) => {
  const { title } = config;
  const { value, label, trend, trendLabel, color = 'blue' } = data;

  const colorClasses = {
    blue: 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    green: 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    yellow: 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
    red: 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
    purple: 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
    pink: 'bg-pink-50 text-pink-600 dark:bg-pink-900/20 dark:text-pink-400',
  };

  const trendColor = trend && trend > 0 ? 'text-green-600' : 'text-red-600';
  const TrendIcon = trend && trend > 0 ? ArrowUpIcon : ArrowDownIcon;

  return (
    <div className="h-full w-full rounded-lg bg-white p-6 shadow-md dark:bg-gray-800">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <p className="text-sm font-medium text-gray-600 dark:text-gray-400">{title || label}</p>
          <p className="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{value}</p>
          {trend !== undefined && (
            <div className="mt-2 flex items-center gap-1">
              <TrendIcon className={`h-4 w-4 ${trendColor}`} />
              <span className={`text-sm font-medium ${trendColor}`}>
                {Math.abs(trend)}%
              </span>
              {trendLabel && (
                <span className="text-sm text-gray-600 dark:text-gray-400">{trendLabel}</span>
              )}
            </div>
          )}
        </div>
        <div className={`rounded-lg p-3 ${colorClasses[color as keyof typeof colorClasses] || colorClasses.blue}`}>
          <svg
            className="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
            />
          </svg>
        </div>
      </div>
    </div>
  );
};
