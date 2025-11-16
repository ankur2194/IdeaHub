import { useEffect } from 'react';
import echo from '../utils/echo';

interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  data: Record<string, any>;
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

interface IdeaCreated {
  id: number;
  title: string;
  description: string;
  user: {
    id: number;
    name: string;
  };
  created_at: string;
}

interface CommentCreated {
  id: number;
  content: string;
  idea_id: number;
  user: {
    id: number;
    name: string;
  };
  created_at: string;
}

interface IdeaApproved {
  id: number;
  title: string;
  status: string;
  approved_at: string;
}

/**
 * Hook to listen to user-specific private channel for real-time notifications
 */
export function useUserNotifications(
  userId: number | null,
  onNotification?: (notification: Notification) => void,
  onBadgeEarned?: (data: BadgeEarned) => void,
  onLevelUp?: (data: UserLeveledUp) => void
) {
  useEffect(() => {
    if (!userId) return;

    const channel = echo.private(`user.${userId}`);

    // Listen for new notifications
    if (onNotification) {
      channel.listen('.notification.new', (data: Notification) => {
        onNotification(data);
      });
    }

    // Listen for badge earned
    if (onBadgeEarned) {
      channel.listen('.badge.earned', (data: BadgeEarned) => {
        onBadgeEarned(data);
      });
    }

    // Listen for level up
    if (onLevelUp) {
      channel.listen('.user.leveled_up', (data: UserLeveledUp) => {
        onLevelUp(data);
      });
    }

    // Cleanup on unmount
    return () => {
      channel.stopListening('.notification.new');
      channel.stopListening('.badge.earned');
      channel.stopListening('.user.leveled_up');
      echo.leaveChannel(`private-user.${userId}`);
    };
  }, [userId, onNotification, onBadgeEarned, onLevelUp]);
}

/**
 * Hook to listen to idea-specific channel for real-time comment updates
 */
export function useIdeaUpdates(
  ideaId: number | null,
  onCommentCreated?: (comment: CommentCreated) => void,
  onIdeaApproved?: (data: IdeaApproved) => void
) {
  useEffect(() => {
    if (!ideaId) return;

    const channel = echo.channel(`idea.${ideaId}`);

    // Listen for new comments
    if (onCommentCreated) {
      channel.listen('.comment.created', (data: CommentCreated) => {
        onCommentCreated(data);
      });
    }

    // Listen for idea approved
    if (onIdeaApproved) {
      channel.listen('.idea.approved', (data: IdeaApproved) => {
        onIdeaApproved(data);
      });
    }

    // Cleanup on unmount
    return () => {
      channel.stopListening('.comment.created');
      channel.stopListening('.idea.approved');
      echo.leaveChannel(`idea.${ideaId}`);
    };
  }, [ideaId, onCommentCreated, onIdeaApproved]);
}

/**
 * Hook to listen to global notifications channel
 */
export function useGlobalNotifications(
  onIdeaCreated?: (idea: IdeaCreated) => void
) {
  useEffect(() => {
    const channel = echo.channel('notifications');

    // Listen for new ideas
    if (onIdeaCreated) {
      channel.listen('.idea.created', (data: IdeaCreated) => {
        onIdeaCreated(data);
      });
    }

    // Cleanup on unmount
    return () => {
      channel.stopListening('.idea.created');
      echo.leaveChannel('notifications');
    };
  }, [onIdeaCreated]);
}

/**
 * Disconnect from all Echo channels
 */
export function disconnectEcho() {
  echo.disconnect();
}
