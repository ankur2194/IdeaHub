import React from 'react';
import {
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
} from '@heroicons/react/24/outline';

interface StatCardProps {
  title: string;
  value: number | string;
  icon: React.ReactNode;
  trend?: number;
  trendLabel?: string;
  color?: 'blue' | 'green' | 'purple' | 'orange' | 'red';
}

const StatCard: React.FC<StatCardProps> = ({
  title,
  value,
  icon,
  trend,
  trendLabel,
  color = 'blue',
}) => {
  const colorClasses = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    purple: 'bg-purple-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
  };

  const trendColor = trend && trend >= 0 ? 'text-green-600' : 'text-red-600';

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between">
        <div className="flex-1">
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="mt-2 text-3xl font-bold text-gray-900">{value}</p>
          {trend !== undefined && (
            <div className={`mt-2 flex items-center text-sm ${trendColor}`}>
              {trend >= 0 ? (
                <ArrowTrendingUpIcon className="h-4 w-4 mr-1" />
              ) : (
                <ArrowTrendingDownIcon className="h-4 w-4 mr-1" />
              )}
              <span className="font-medium">
                {Math.abs(trend)}%
              </span>
              {trendLabel && (
                <span className="ml-1 text-gray-500">{trendLabel}</span>
              )}
            </div>
          )}
        </div>
        <div className={`${colorClasses[color]} p-3 rounded-lg`}>
          {icon}
        </div>
      </div>
    </div>
  );
};

export default StatCard;
