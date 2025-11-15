import React, { useEffect, useState } from 'react';
import { Tab } from '@headlessui/react';
import BadgeCard from './BadgeCard';
import { gamificationService, type BadgeProgress } from '../../services/gamificationService';

const BadgeGallery: React.FC = () => {
  const [badgeProgress, setBadgeProgress] = useState<BadgeProgress[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'all' | 'earned' | 'locked'>('all');

  useEffect(() => {
    loadBadges();
  }, []);

  const loadBadges = async () => {
    try {
      const response = await gamificationService.getBadgeProgress();
      setBadgeProgress(response.data);
    } catch (error) {
      console.error('Failed to load badge progress:', error);
    } finally {
      setLoading(false);
    }
  };

  const categories = [
    { key: 'all', label: 'All Badges' },
    { key: 'ideas', label: 'Ideas' },
    { key: 'approvals', label: 'Approvals' },
    { key: 'comments', label: 'Comments' },
    { key: 'likes', label: 'Likes' },
    { key: 'levels', label: 'Milestones' },
  ];

  const filteredBadges = (category: string) => {
    let badges = badgeProgress;

    // Filter by category
    if (category !== 'all') {
      badges = badges.filter((bp) => bp.badge.category === category);
    }

    // Filter by earned/locked status
    if (filter === 'earned') {
      badges = badges.filter((bp) => bp.earned);
    } else if (filter === 'locked') {
      badges = badges.filter((bp) => !bp.earned);
    }

    return badges;
  };

  const earnedCount = badgeProgress.filter((bp) => bp.earned).length;
  const totalCount = badgeProgress.length;

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Badge Collection</h2>
          <p className="text-sm text-gray-600">
            {earnedCount} of {totalCount} badges earned ({Math.round((earnedCount / totalCount) * 100)}%)
          </p>
        </div>

        {/* Filter Toggle */}
        <div className="flex gap-2">
          <button
            onClick={() => setFilter('all')}
            className={`rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
              filter === 'all'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            All
          </button>
          <button
            onClick={() => setFilter('earned')}
            className={`rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
              filter === 'earned'
                ? 'bg-green-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Earned ({earnedCount})
          </button>
          <button
            onClick={() => setFilter('locked')}
            className={`rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
              filter === 'locked'
                ? 'bg-gray-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Locked ({totalCount - earnedCount})
          </button>
        </div>
      </div>

      {/* Category Tabs */}
      <Tab.Group>
        <Tab.List className="flex space-x-2 rounded-lg bg-gray-100 p-1">
          {categories.map((category) => (
            <Tab
              key={category.key}
              className={({ selected }) =>
                `w-full rounded-md py-2 text-sm font-medium transition-all ${
                  selected
                    ? 'bg-white text-blue-600 shadow'
                    : 'text-gray-600 hover:bg-white/50 hover:text-gray-900'
                }`
              }
            >
              {category.label}
            </Tab>
          ))}
        </Tab.List>

        <Tab.Panels className="mt-6">
          {categories.map((category) => (
            <Tab.Panel key={category.key}>
              {filteredBadges(category.key).length === 0 ? (
                <div className="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
                  <p className="text-sm text-gray-500">
                    No badges found in this category
                  </p>
                </div>
              ) : (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                  {filteredBadges(category.key).map((badgeProgress) => {
                    // Calculate overall progress percentage
                    const overallProgress =
                      badgeProgress.progress.length > 0
                        ? badgeProgress.progress.reduce((sum, p) => sum + p.percentage, 0) /
                          badgeProgress.progress.length
                        : 0;

                    return (
                      <BadgeCard
                        key={badgeProgress.badge.id}
                        badge={badgeProgress.badge}
                        earned={badgeProgress.earned}
                        earnedAt={badgeProgress.earned_at}
                        progress={overallProgress}
                        showProgress={!badgeProgress.earned}
                      />
                    );
                  })}
                </div>
              )}
            </Tab.Panel>
          ))}
        </Tab.Panels>
      </Tab.Group>
    </div>
  );
};

export default BadgeGallery;
