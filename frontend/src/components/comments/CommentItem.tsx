import { useState } from 'react';
import type { Comment } from '../../types';
import { formatDate } from '../../utils/formatters';
import Avatar from '../common/Avatar';
import { HeartIcon, TrashIcon, PencilIcon } from '@heroicons/react/24/outline';
import { HeartIcon as HeartSolidIcon } from '@heroicons/react/24/solid';

interface CommentItemProps {
  comment: Comment;
  onLike?: (id: number) => void;
  onDelete?: (id: number) => void;
  onEdit?: (id: number, content: string) => void;
  currentUserId?: number;
  isLiked?: boolean;
}

const CommentItem: React.FC<CommentItemProps> = ({
  comment,
  onLike,
  onDelete,
  onEdit,
  currentUserId,
  isLiked = false,
}) => {
  const [isEditing, setIsEditing] = useState(false);
  const [editContent, setEditContent] = useState(comment.content);

  const isOwner = currentUserId === comment.user_id;

  const handleSaveEdit = () => {
    if (editContent.trim() && onEdit) {
      onEdit(comment.id, editContent);
      setIsEditing(false);
    }
  };

  const handleCancelEdit = () => {
    setEditContent(comment.content);
    setIsEditing(false);
  };

  return (
    <div className="flex space-x-3">
      <div className="flex-shrink-0">
        {comment.user && (
          <Avatar name={comment.user.name} src={comment.user.avatar} size="md" />
        )}
      </div>
      <div className="flex-1 min-w-0">
        <div className="text-sm">
          <span className="font-medium text-gray-900">
            {comment.user?.name || 'Unknown User'}
          </span>
          {comment.is_edited && (
            <span className="ml-2 text-xs text-gray-500">(edited)</span>
          )}
        </div>
        <div className="mt-1 text-sm text-gray-500">
          {formatDate(comment.created_at)}
        </div>
        <div className="mt-2">
          {isEditing ? (
            <div className="space-y-2">
              <textarea
                value={editContent}
                onChange={(e) => setEditContent(e.target.value)}
                rows={3}
                className="block w-full rounded-md border-0 px-3 py-2 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600"
              />
              <div className="flex space-x-2">
                <button
                  onClick={handleSaveEdit}
                  className="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"
                >
                  Save
                </button>
                <button
                  onClick={handleCancelEdit}
                  className="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                >
                  Cancel
                </button>
              </div>
            </div>
          ) : (
            <p className="text-sm text-gray-700 whitespace-pre-wrap">{comment.content}</p>
          )}
        </div>
        <div className="mt-2 flex items-center space-x-4">
          <button
            onClick={() => onLike?.(comment.id)}
            className="flex items-center space-x-1 text-sm text-gray-500 hover:text-red-600 transition-colors"
          >
            {isLiked ? (
              <HeartSolidIcon className="h-4 w-4 text-red-600" />
            ) : (
              <HeartIcon className="h-4 w-4" />
            )}
            <span>{comment.likes_count || 0}</span>
          </button>
          {isOwner && !isEditing && (
            <>
              <button
                onClick={() => setIsEditing(true)}
                className="flex items-center space-x-1 text-sm text-gray-500 hover:text-blue-600 transition-colors"
              >
                <PencilIcon className="h-4 w-4" />
                <span>Edit</span>
              </button>
              <button
                onClick={() => onDelete?.(comment.id)}
                className="flex items-center space-x-1 text-sm text-gray-500 hover:text-red-600 transition-colors"
              >
                <TrashIcon className="h-4 w-4" />
                <span>Delete</span>
              </button>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default CommentItem;
