import { Fragment, useState, useEffect } from 'react';
import { Menu, Transition } from '@headlessui/react';
import { BellIcon } from '@heroicons/react/24/outline';
import { useUserNotifications } from '../../hooks/useEcho';
import { useAppSelector } from '../../store/hooks';
import { Link } from 'react-router-dom';

interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  data: Record<string, unknown>;
  read: boolean;
  created_at: string;
}

interface BadgeEarned {
  badge: {
    id: number;
    name: string;
    description: string;
    icon: string;
    rarity: string;
    points_reward: number;
  };
  user: {
    id: number;
    name: string;
    total_badges: number;
  };
}

interface UserLeveledUp {
  user: {
    id: number;
    name: string;
  };
  old_level: number;
  new_level: number;
  title: string;
  experience: number;
}

const NotificationDropdown: React.FC = () => {
  const { user } = useAppSelector((state) => state.auth);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [showToast, setShowToast] = useState(false);
  const [latestNotification, setLatestNotification] = useState<string | null>(null);

  // Real-time notification listeners
  useUserNotifications(
    user?.id || null,
    // On new notification
    (notification) => {
      setNotifications((prev) => [notification, ...prev].slice(0, 10)); // Keep last 10
      setUnreadCount((count) => count + 1);
      setLatestNotification(notification.title);
      setShowToast(true);
      setTimeout(() => setShowToast(false), 5000);
    },
    // On badge earned
    (data: BadgeEarned) => {
      const notif: Notification = {
        id: Date.now(),
        type: 'badge_earned',
        title: '🏆 New Badge Earned!',
        message: `You've earned the '${data.badge.name}' badge!`,
        data: { badge: data.badge },
        read: false,
        created_at: new Date().toISOString(),
      };
      setNotifications((prev) => [notif, ...prev].slice(0, 10));
      setUnreadCount((count) => count + 1);
      setLatestNotification(notif.title);
      setShowToast(true);
      setTimeout(() => setShowToast(false), 5000);
    },
    // On level up
    (data: UserLeveledUp) => {
      const notif: Notification = {
        id: Date.now(),
        type: 'level_up',
        title: '🎉 Level Up!',
        message: `Congratulations! You've reached Level ${data.new_level} - ${data.title}`,
        data: { level: data.new_level, title: data.title },
        read: false,
        created_at: new Date().toISOString(),
      };
      setNotifications((prev) => [notif, ...prev].slice(0, 10));
      setUnreadCount((count) => count + 1);
      setLatestNotification(notif.title);
      setShowToast(true);
      setTimeout(() => setShowToast(false), 5000);
    }
  );

  // Fetch initial notifications
  useEffect(() => {
    // TODO: Fetch from API
    // For now, using local state only
  }, []);

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'badge_earned':
        return '🏆';
      case 'level_up':
        return '🎉';
      case 'idea_approved':
        return '✅';
      case 'idea_rejected':
        return '❌';
      case 'comment_posted':
      case 'comment_reply':
        return '💬';
      case 'approval_request':
        return '⏳';
      default:
        return '🔔';
    }
  };

  const getNotificationLink = (notification: Notification) => {
    const data = notification.data as { idea_id?: number };
    if (data.idea_id) {
      return `/ideas/${data.idea_id}`;
    }
    if (notification.type === 'badge_earned' || notification.type === 'level_up') {
      return '/profile';
    }
    return '/dashboard';
  };

  const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    return `${days}d ago`;
  };

  return (
    <>
      {/* Toast notification */}
      {showToast && latestNotification && (
        <div className="fixed right-4 top-20 z-50 animate-slide-in-right">
          <div className="rounded-lg bg-blue-600 p-4 text-white shadow-lg">
            <p className="font-medium">{latestNotification}</p>
          </div>
        </div>
      )}

      {/* Notification dropdown */}
      <Menu as="div" className="relative">
        <Menu.Button className="relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          <BellIcon className="h-6 w-6" />
          {unreadCount > 0 && (
            <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
              {unreadCount > 9 ? '9+' : unreadCount}
            </span>
          )}
        </Menu.Button>

        <Transition
          as={Fragment}
          enter="transition ease-out duration-100"
          enterFrom="transform opacity-0 scale-95"
          enterTo="transform opacity-100 scale-100"
          leave="transition ease-in duration-75"
          leaveFrom="transform opacity-100 scale-100"
          leaveTo="transform opacity-0 scale-95"
        >
          <Menu.Items className="absolute right-0 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
            <div className="p-2">
              <div className="mb-2 flex items-center justify-between border-b px-2 pb-2">
                <h3 className="text-sm font-semibold text-gray-900">Notifications</h3>
                {unreadCount > 0 && (
                  <button
                    onClick={() => {
                      setNotifications((prev) =>
                        prev.map((n) => ({ ...n, read: true }))
                      );
                      setUnreadCount(0);
                    }}
                    className="text-xs text-blue-600 hover:text-blue-800"
                  >
                    Mark all read
                  </button>
                )}
              </div>

              {notifications.length === 0 ? (
                <div className="py-8 text-center">
                  <BellIcon className="mx-auto h-12 w-12 text-gray-300" />
                  <p className="mt-2 text-sm text-gray-500">No notifications yet</p>
                </div>
              ) : (
                <div className="max-h-96 overflow-y-auto">
                  {notifications.map((notification) => (
                    <Menu.Item key={notification.id}>
                      {({ active }) => (
                        <Link
                          to={getNotificationLink(notification)}
                          className={`${
                            active ? 'bg-gray-50' : ''
                          } ${
                            !notification.read ? 'bg-blue-50' : ''
                          } block rounded-md px-3 py-2 transition-colors`}
                          onClick={() => {
                            if (!notification.read) {
                              setNotifications((prev) =>
                                prev.map((n) =>
                                  n.id === notification.id ? { ...n, read: true } : n
                                )
                              );
                              setUnreadCount((count) => Math.max(0, count - 1));
                            }
                          }}
                        >
                          <div className="flex items-start">
                            <span className="text-2xl">{getNotificationIcon(notification.type)}</span>
                            <div className="ml-3 flex-1">
                              <p className="text-sm font-medium text-gray-900">
                                {notification.title}
                              </p>
                              <p className="mt-1 text-xs text-gray-600">
                                {notification.message}
                              </p>
                              <p className="mt-1 text-xs text-gray-400">
                                {formatTime(notification.created_at)}
                              </p>
                            </div>
                          </div>
                        </Link>
                      )}
                    </Menu.Item>
                  ))}
                </div>
              )}

              {notifications.length > 0 && (
                <div className="mt-2 border-t pt-2 text-center">
                  <Link
                    to="/notifications"
                    className="text-xs text-blue-600 hover:text-blue-800"
                  >
                    View all notifications
                  </Link>
                </div>
              )}
            </div>
          </Menu.Items>
        </Transition>
      </Menu>
    </>
  );
};

export default NotificationDropdown;
