import { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchIdea, likeIdea, deleteIdea, submitIdea } from '../store/ideasSlice';
import { commentService } from '../services/commentService';
import { useIdeaUpdates } from '../hooks/useEcho';
import StatusBadge from '../components/common/StatusBadge';
import CategoryBadge from '../components/common/CategoryBadge';
import TagBadge from '../components/common/TagBadge';
import Avatar from '../components/common/Avatar';
import CommentList from '../components/comments/CommentList';
import CommentForm from '../components/comments/CommentForm';
import { AttachmentList } from '../components/AttachmentList';
import WorkflowStatus from '../components/approvals/WorkflowStatus';
import { formatDateTime } from '../utils/formatters';
import { canEditIdea, canDeleteIdea, canSubmitIdea } from '../utils/statusHelpers';
import {
  HeartIcon as HeartIconOutline,
  ChatBubbleLeftIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  PaperAirplaneIcon,
  ArrowLeftIcon,
} from '@heroicons/react/24/outline';
import { HeartIcon as HeartIconSolid } from '@heroicons/react/24/solid';
import type { Comment } from '../types';

const IdeaDetail = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { currentIdea: idea, loading } = useAppSelector((state) => state.ideas);
  const { user } = useAppSelector((state) => state.auth);

  const [comments, setComments] = useState<Comment[]>([]);
  const [commentsLoading, setCommentsLoading] = useState(false);
  const [commentSubmitting, setCommentSubmitting] = useState(false);

  const loadComments = useCallback(async () => {
    if (!id) return;
    setCommentsLoading(true);
    try {
      const response = await commentService.getComments(parseInt(id));
      setComments(response.data);
    } catch (error) {
      console.error('Failed to load comments:', error);
    } finally {
      setCommentsLoading(false);
    }
  }, [id]);

  // Real-time updates for this idea
  useIdeaUpdates(
    idea?.id || null,
    // On new comment created
    (commentData) => {
      // Only add if it's not from the current user (avoid duplicates)
      if (commentData.user.id !== user?.id) {
        // Convert broadcast data to full Comment type
        const newComment: Comment = {
          id: commentData.id,
          content: commentData.content,
          idea_id: commentData.idea_id,
          user_id: commentData.user.id,
          parent_id: null,
          likes_count: 0,
          is_edited: false,
          created_at: commentData.created_at,
          updated_at: commentData.created_at,
          user: {
            id: commentData.user.id,
            name: commentData.user.name,
          } as Comment['user'],
        };
        setComments((prev) => [newComment, ...prev]);
      }
    },
    // On idea approved
    () => {
      if (idea) {
        // Refresh the idea to get updated status
        dispatch(fetchIdea(idea.id));
      }
    }
  );

  useEffect(() => {
    if (id) {
      dispatch(fetchIdea(parseInt(id)));
      loadComments();
    }
  }, [id, dispatch, loadComments]);

  const handleLike = () => {
    if (idea) {
      dispatch(likeIdea(idea.id));
    }
  };

  const handleDelete = async () => {
    if (!idea || !window.confirm('Are you sure you want to delete this idea?')) return;
    try {
      await dispatch(deleteIdea(idea.id)).unwrap();
      navigate('/ideas');
    } catch (error) {
      console.error('Failed to delete idea:', error);
    }
  };

  const handleSubmit = async () => {
    if (!idea || !window.confirm('Submit this idea for review?')) return;
    try {
      await dispatch(submitIdea(idea.id)).unwrap();
    } catch (error) {
      console.error('Failed to submit idea:', error);
    }
  };

  const handleCommentSubmit = async (content: string) => {
    if (!idea) return;
    setCommentSubmitting(true);
    try {
      const response = await commentService.createComment({
        idea_id: idea.id,
        content,
      });
      setComments([response.data, ...comments]);
    } catch (error) {
      console.error('Failed to post comment:', error);
    } finally {
      setCommentSubmitting(false);
    }
  };

  const handleCommentLike = async (commentId: number) => {
    try {
      const response = await commentService.likeComment(commentId);
      setComments(
        comments.map((c) =>
          c.id === commentId ? { ...c, likes_count: response.data.likes_count } : c
        )
      );
    } catch (error) {
      console.error('Failed to like comment:', error);
    }
  };

  const handleCommentDelete = async (commentId: number) => {
    if (!window.confirm('Are you sure you want to delete this comment?')) return;
    try {
      await commentService.deleteComment(commentId);
      setComments(comments.filter((c) => c.id !== commentId));
    } catch (error) {
      console.error('Failed to delete comment:', error);
    }
  };

  const handleCommentEdit = async (commentId: number, content: string) => {
    try {
      const response = await commentService.updateComment(commentId, content);
      setComments(comments.map((c) => (c.id === commentId ? response.data : c)));
    } catch (error) {
      console.error('Failed to edit comment:', error);
    }
  };

  if (loading || !idea) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-blue-600"></div>
      </div>
    );
  }

  const isOwner = user?.id === idea.user_id;

  return (
    <div className="mx-auto max-w-4xl">
      {/* Back button */}
      <Link
        to="/ideas"
        className="mb-6 inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
      >
        <ArrowLeftIcon className="mr-2 h-4 w-4" />
        Back to ideas
      </Link>

      {/* Idea content */}
      <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
        {/* Header */}
        <div className="border-b border-gray-200 p-6">
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <div className="flex items-center space-x-3">
                <h1 className="text-2xl font-bold text-gray-900">{idea.title}</h1>
                <StatusBadge status={idea.status} />
              </div>
              <div className="mt-4 flex items-center space-x-4">
                {idea.user && !idea.is_anonymous && (
                  <>
                    <Avatar name={idea.user.name} src={idea.user.avatar} size="md" />
                    <div>
                      <p className="text-sm font-medium text-gray-900">{idea.user.name}</p>
                      <p className="text-xs text-gray-500">
                        {idea.user.department && `${idea.user.department} • `}
                        {formatDateTime(idea.created_at)}
                      </p>
                    </div>
                  </>
                )}
                {idea.is_anonymous && (
                  <div>
                    <p className="text-sm font-medium text-gray-900">Anonymous</p>
                    <p className="text-xs text-gray-500">{formatDateTime(idea.created_at)}</p>
                  </div>
                )}
              </div>
            </div>

            {/* Action buttons */}
            {isOwner && (
              <div className="flex space-x-2">
                {canSubmitIdea(idea.status, isOwner) && (
                  <button
                    onClick={handleSubmit}
                    className="rounded-md bg-green-600 p-2 text-white hover:bg-green-700"
                    title="Submit for review"
                  >
                    <PaperAirplaneIcon className="h-5 w-5" />
                  </button>
                )}
                {canEditIdea(idea.status, isOwner) && (
                  <Link
                    to={`/ideas/${idea.id}/edit`}
                    className="rounded-md bg-blue-600 p-2 text-white hover:bg-blue-700"
                    title="Edit"
                  >
                    <PencilIcon className="h-5 w-5" />
                  </Link>
                )}
                {canDeleteIdea(idea.status, isOwner) && (
                  <button
                    onClick={handleDelete}
                    className="rounded-md bg-red-600 p-2 text-white hover:bg-red-700"
                    title="Delete"
                  >
                    <TrashIcon className="h-5 w-5" />
                  </button>
                )}
              </div>
            )}
          </div>

          {/* Category and tags */}
          <div className="mt-4 flex flex-wrap gap-2">
            {idea.category && <CategoryBadge category={idea.category} />}
            {idea.tags?.map((tag) => (
              <TagBadge key={tag.id} tag={tag} />
            ))}
          </div>

          {/* Stats */}
          <div className="mt-4 flex items-center space-x-6 text-sm text-gray-500">
            <button
              onClick={handleLike}
              className={`flex items-center space-x-1 hover:text-red-600 transition-colors ${
                idea.liked ? 'text-red-600' : ''
              }`}
            >
              {idea.liked ? (
                <HeartIconSolid className="h-5 w-5" />
              ) : (
                <HeartIconOutline className="h-5 w-5" />
              )}
              <span>{idea.likes_count || 0} likes</span>
            </button>
            <div className="flex items-center space-x-1">
              <ChatBubbleLeftIcon className="h-5 w-5" />
              <span>{idea.comments_count || 0} comments</span>
            </div>
            <div className="flex items-center space-x-1">
              <EyeIcon className="h-5 w-5" />
              <span>{idea.views_count || 0} views</span>
            </div>
          </div>
        </div>

        {/* Description */}
        <div className="p-6">
          <h2 className="mb-3 text-lg font-semibold text-gray-900">Description</h2>
          <div className="prose max-w-none text-gray-700 whitespace-pre-wrap">
            {idea.description}
          </div>
        </div>

        {/* Attachments */}
        {idea.attachments && idea.attachments.length > 0 && (
          <div className="border-t border-gray-200 p-6">
            <AttachmentList attachments={idea.attachments} />
          </div>
        )}

        {/* Workflow Status */}
        {(idea.status === 'submitted' || idea.status === 'under_review' || idea.status === 'approved' || idea.status === 'rejected') && (
          <div className="border-t border-gray-200 p-6">
            <WorkflowStatus ideaId={idea.id} />
          </div>
        )}

        {/* Comments section */}
        <div className="border-t border-gray-200 p-6">
          <h2 className="mb-4 text-lg font-semibold text-gray-900">
            Comments ({comments.length})
          </h2>

          {/* Comment form */}
          <div className="mb-6">
            <CommentForm
              ideaId={idea.id}
              onSubmit={handleCommentSubmit}
              loading={commentSubmitting}
            />
          </div>

          {/* Comments list */}
          {commentsLoading ? (
            <div className="flex items-center justify-center py-8">
              <div className="h-6 w-6 animate-spin rounded-full border-b-2 border-t-2 border-blue-600"></div>
            </div>
          ) : (
            <CommentList
              comments={comments}
              onLike={handleCommentLike}
              onDelete={handleCommentDelete}
              onEdit={handleCommentEdit}
              currentUserId={user?.id}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default IdeaDetail;
