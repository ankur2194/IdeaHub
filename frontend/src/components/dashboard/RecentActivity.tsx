import React from 'react';
import { Link } from 'react-router-dom';
import {
  ClockIcon,
  LightBulbIcon,
} from '@heroicons/react/24/outline';
import Avatar from '../common/Avatar';
import StatusBadge from '../common/StatusBadge';
import type { RecentActivity as RecentActivityType } from '../../services/analyticsService';

interface RecentActivityProps {
  activities: RecentActivityType[];
  loading?: boolean;
}

const RecentActivity: React.FC<RecentActivityProps> = ({
  activities,
  loading,
}) => {
  if (loading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">
          Recent Activity
        </h3>
        <div className="space-y-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="animate-pulse flex items-start gap-3">
              <div className="w-10 h-10 bg-gray-200 rounded-full"></div>
              <div className="flex-1">
                <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <ClockIcon className="h-5 w-5 text-gray-500" />
        Recent Activity
      </h3>

      {activities.length === 0 ? (
        <p className="text-center text-gray-500 py-8">No recent activity</p>
      ) : (
        <div className="space-y-4">
          {activities.map((activity) => (
            <Link
              key={`${activity.type}-${activity.id}`}
              to={`/ideas/${activity.id}`}
              className="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group"
            >
              {/* Icon */}
              <div className="flex-shrink-0">
                {activity.type === 'idea' ? (
                  <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <LightBulbIcon className="h-5 w-5 text-purple-600" />
                  </div>
                ) : (
                  <Avatar
                    name={activity.user.name}
                    src={activity.user.avatar}
                    size="md"
                  />
                )}
              </div>

              {/* Content */}
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors truncate">
                  {activity.title}
                </p>
                <div className="flex items-center gap-2 mt-1">
                  <p className="text-xs text-gray-500">
                    by {activity.user.name}
                  </p>
                  {activity.category && (
                    <>
                      <span className="text-gray-300">•</span>
                      <p className="text-xs text-gray-500">{activity.category}</p>
                    </>
                  )}
                </div>
                <p className="text-xs text-gray-400 mt-1">{activity.timestamp}</p>
              </div>

              {/* Status Badge */}
              <div className="flex-shrink-0">
                <StatusBadge status={activity.status} />
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
};

export default RecentActivity;
