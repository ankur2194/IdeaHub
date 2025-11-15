import React from 'react';
import { Tab } from '@headlessui/react';
import { useAppSelector } from '../store/hooks';
import UserGamificationCard from '../components/gamification/UserGamificationCard';
import BadgeGallery from '../components/gamification/BadgeGallery';
import {
  UserCircleIcon,
  TrophyIcon,
  ChartBarIcon,
} from '@heroicons/react/24/outline';

const Profile: React.FC = () => {
  const { user } = useAppSelector((state) => state.auth);

  if (!user) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-gray-500">Please log in to view your profile</p>
      </div>
    );
  }

  const tabs = [
    { name: 'Profile', icon: UserCircleIcon },
    { name: 'Badges', icon: TrophyIcon },
    { name: 'Stats', icon: ChartBarIcon },
  ];

  return (
    <div className="mx-auto max-w-6xl space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">My Profile</h1>
          <p className="text-gray-600">View your progress and achievements</p>
        </div>
      </div>

      {/* Tabs */}
      <Tab.Group>
        <Tab.List className="flex space-x-1 rounded-lg bg-gray-100 p-1">
          {tabs.map((tab) => (
            <Tab
              key={tab.name}
              className={({ selected }) =>
                `flex w-full items-center justify-center gap-2 rounded-md py-2.5 text-sm font-medium leading-5 transition-all ${
                  selected
                    ? 'bg-white text-blue-600 shadow'
                    : 'text-gray-600 hover:bg-white/50 hover:text-gray-900'
                }`
              }
            >
              <tab.icon className="h-5 w-5" />
              {tab.name}
            </Tab>
          ))}
        </Tab.List>

        <Tab.Panels className="mt-6">
          {/* Profile Tab */}
          <Tab.Panel>
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
              {/* Left Column - User Info */}
              <div className="lg:col-span-1">
                <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                  <div className="space-y-4">
                    {/* Avatar */}
                    <div className="flex justify-center">
                      {user.avatar ? (
                        <img
                          src={user.avatar}
                          alt={user.name}
                          className="h-24 w-24 rounded-full border-4 border-gray-200"
                        />
                      ) : (
                        <div className="flex h-24 w-24 items-center justify-center rounded-full border-4 border-gray-200 bg-gradient-to-br from-blue-500 to-purple-600 text-3xl font-bold text-white">
                          {user.name.charAt(0).toUpperCase()}
                        </div>
                      )}
                    </div>

                    {/* User Details */}
                    <div className="text-center">
                      <h2 className="text-xl font-bold text-gray-900">{user.name}</h2>
                      <p className="text-sm text-gray-600">{user.email}</p>
                      {user.department && (
                        <p className="mt-1 text-sm text-gray-500">{user.department}</p>
                      )}
                      {user.job_title && (
                        <p className="text-xs text-gray-500">{user.job_title}</p>
                      )}
                    </div>

                    {/* Role Badge */}
                    <div className="flex justify-center">
                      <span className="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 capitalize">
                        {user.role.replace('_', ' ')}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Right Column - Gamification */}
              <div className="lg:col-span-2">
                <UserGamificationCard />
              </div>
            </div>
          </Tab.Panel>

          {/* Badges Tab */}
          <Tab.Panel>
            <BadgeGallery />
          </Tab.Panel>

          {/* Stats Tab */}
          <Tab.Panel>
            <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
              <h2 className="mb-4 text-xl font-bold text-gray-900">Detailed Statistics</h2>
              <p className="text-gray-600">
                Detailed statistics and analytics coming soon...
              </p>
            </div>
          </Tab.Panel>
        </Tab.Panels>
      </Tab.Group>
    </div>
  );
};

export default Profile;
