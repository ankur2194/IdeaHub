import type { IdeaStatus } from '../types';

/**
 * Get status badge color based on idea status
 */
export const getStatusColor = (status: IdeaStatus): string => {
  const colors: Record<IdeaStatus, string> = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    under_review: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    implemented: 'bg-purple-100 text-purple-800',
    archived: 'bg-gray-100 text-gray-600',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

/**
 * Get status label
 */
export const getStatusLabel = (status: IdeaStatus): string => {
  const labels: Record<IdeaStatus, string> = {
    draft: 'Draft',
    submitted: 'Submitted',
    under_review: 'Under Review',
    approved: 'Approved',
    rejected: 'Rejected',
    implemented: 'Implemented',
    archived: 'Archived',
  };
  return labels[status] || status;
};

/**
 * Check if user can edit idea based on status
 */
export const canEditIdea = (status: IdeaStatus, isOwner: boolean): boolean => {
  if (!isOwner) return false;
  return ['draft', 'rejected'].includes(status);
};

/**
 * Check if user can delete idea based on status
 */
export const canDeleteIdea = (status: IdeaStatus, isOwner: boolean): boolean => {
  if (!isOwner) return false;
  return status === 'draft';
};

/**
 * Check if idea can be submitted
 */
export const canSubmitIdea = (status: IdeaStatus, isOwner: boolean): boolean => {
  if (!isOwner) return false;
  return status === 'draft';
};
