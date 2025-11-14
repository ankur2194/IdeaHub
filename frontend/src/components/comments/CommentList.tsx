import type { Comment } from '../../types';
import CommentItem from './CommentItem';

interface CommentListProps {
  comments: Comment[];
  onLike?: (id: number) => void;
  onDelete?: (id: number) => void;
  onEdit?: (id: number, content: string) => void;
  currentUserId?: number;
}

const CommentList: React.FC<CommentListProps> = ({
  comments,
  onLike,
  onDelete,
  onEdit,
  currentUserId,
}) => {
  if (comments.length === 0) {
    return (
      <div className="text-center py-8">
        <p className="text-sm text-gray-500">No comments yet. Be the first to comment!</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {comments.map((comment) => (
        <CommentItem
          key={comment.id}
          comment={comment}
          onLike={onLike}
          onDelete={onDelete}
          onEdit={onEdit}
          currentUserId={currentUserId}
        />
      ))}
    </div>
  );
};

export default CommentList;
