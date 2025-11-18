import { useEffect, useRef } from 'react';
import echo from '../utils/echo';

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
 * Uses refs to prevent memory leaks from callback dependencies
 */
export function useUserNotifications(
  userId: number | null,
  onNotification?: (notification: Notification) => void,
  onBadgeEarned?: (data: BadgeEarned) => void,
  onLevelUp?: (data: UserLeveledUp) => void
) {
  // Use refs to store latest callbacks without causing re-renders
  const onNotificationRef = useRef(onNotification);
  const onBadgeEarnedRef = useRef(onBadgeEarned);
  const onLevelUpRef = useRef(onLevelUp);

  // Update refs when callbacks change
  useEffect(() => {
    onNotificationRef.current = onNotification;
    onBadgeEarnedRef.current = onBadgeEarned;
    onLevelUpRef.current = onLevelUp;
  }, [onNotification, onBadgeEarned, onLevelUp]);

  useEffect(() => {
    if (!userId) return;

    const channel = echo.private(`user.${userId}`);

    // Listen for new notifications
    if (onNotificationRef.current) {
      channel.listen('.notification.new', (data: Notification) => {
        onNotificationRef.current?.(data);
      });
    }

    // Listen for badge earned
    if (onBadgeEarnedRef.current) {
      channel.listen('.badge.earned', (data: BadgeEarned) => {
        onBadgeEarnedRef.current?.(data);
      });
    }

    // Listen for level up
    if (onLevelUpRef.current) {
      channel.listen('.user.leveled_up', (data: UserLeveledUp) => {
        onLevelUpRef.current?.(data);
      });
    }

    // Cleanup on unmount
    return () => {
      channel.stopListening('.notification.new');
      channel.stopListening('.badge.earned');
      channel.stopListening('.user.leveled_up');
      echo.leaveChannel(`private-user.${userId}`);
    };
  }, [userId]); // Only depend on userId, not the callbacks
}

/**
 * Hook to listen to idea-specific channel for real-time comment updates
 * Uses refs to prevent memory leaks from callback dependencies
 */
export function useIdeaUpdates(
  ideaId: number | null,
  onCommentCreated?: (comment: CommentCreated) => void,
  onIdeaApproved?: (data: IdeaApproved) => void
) {
  // Use refs to store latest callbacks without causing re-renders
  const onCommentCreatedRef = useRef(onCommentCreated);
  const onIdeaApprovedRef = useRef(onIdeaApproved);

  // Update refs when callbacks change
  useEffect(() => {
    onCommentCreatedRef.current = onCommentCreated;
    onIdeaApprovedRef.current = onIdeaApproved;
  }, [onCommentCreated, onIdeaApproved]);

  useEffect(() => {
    if (!ideaId) return;

    const channel = echo.channel(`idea.${ideaId}`);

    // Listen for new comments
    if (onCommentCreatedRef.current) {
      channel.listen('.comment.created', (data: CommentCreated) => {
        onCommentCreatedRef.current?.(data);
      });
    }

    // Listen for idea approved
    if (onIdeaApprovedRef.current) {
      channel.listen('.idea.approved', (data: IdeaApproved) => {
        onIdeaApprovedRef.current?.(data);
      });
    }

    // Cleanup on unmount
    return () => {
      channel.stopListening('.comment.created');
      channel.stopListening('.idea.approved');
      echo.leaveChannel(`idea.${ideaId}`);
    };
  }, [ideaId]); // Only depend on ideaId, not the callbacks
}

/**
 * Hook to listen to global notifications channel
 * Uses ref to prevent memory leaks from callback dependencies
 */
export function useGlobalNotifications(
  onIdeaCreated?: (idea: IdeaCreated) => void
) {
  // Use ref to store latest callback without causing re-renders
  const onIdeaCreatedRef = useRef(onIdeaCreated);

  // Update ref when callback changes
  useEffect(() => {
    onIdeaCreatedRef.current = onIdeaCreated;
  }, [onIdeaCreated]);

  useEffect(() => {
    const channel = echo.channel('notifications');

    // Listen for new ideas
    if (onIdeaCreatedRef.current) {
      channel.listen('.idea.created', (data: IdeaCreated) => {
        onIdeaCreatedRef.current?.(data);
      });
    }

    // Cleanup on unmount
    return () => {
      channel.stopListening('.idea.created');
      echo.leaveChannel('notifications');
    };
  }, []); // No dependencies - only run once on mount/unmount
}

/**
 * Disconnect from all Echo channels
 */
export function disconnectEcho() {
  echo.disconnect();
}
