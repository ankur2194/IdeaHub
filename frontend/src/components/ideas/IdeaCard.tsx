import { Link } from 'react-router-dom';
import type { Idea } from '../../types';
import { formatDate, truncateText } from '../../utils/formatters';
import StatusBadge from '../common/StatusBadge';
import CategoryBadge from '../common/CategoryBadge';
import TagBadge from '../common/TagBadge';
import Avatar from '../common/Avatar';
import { HeartIcon, ChatBubbleLeftIcon, EyeIcon } from '@heroicons/react/24/outline';
import { HeartIcon as HeartSolidIcon } from '@heroicons/react/24/solid';

interface IdeaCardProps {
  idea: Idea;
  onLike?: (id: number) => void;
  isLiked?: boolean;
}

const IdeaCard: React.FC<IdeaCardProps> = ({ idea, onLike, isLiked = false }) => {
  const handleLike = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation(); // Prevent event from bubbling to Link
    onLike?.(idea.id);
  };

  return (
    <Link
      to={`/ideas/${idea.id}`}
      className="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
    >
      <div className="mb-4 flex items-start justify-between">
        <div className="flex-1">
          <h3 className="text-lg font-semibold text-gray-900 hover:text-blue-600">
            {idea.title}
          </h3>
          <p className="mt-2 text-sm text-gray-600">
            {truncateText(idea.description, 150)}
          </p>
        </div>
        <StatusBadge status={idea.status} className="ml-4" />
      </div>

      <div className="mb-4 flex flex-wrap gap-2">
        {idea.category && <CategoryBadge category={idea.category} />}
        {idea.tags?.slice(0, 3).map((tag) => (
          <TagBadge key={tag.id} tag={tag} />
        ))}
        {idea.tags && idea.tags.length > 3 && (
          <span className="text-xs text-gray-500">+{idea.tags.length - 3} more</span>
        )}
      </div>

      <div className="flex items-center justify-between border-t border-gray-100 pt-4">
        <div className="flex items-center space-x-2">
          {idea.user && !idea.is_anonymous && (
            <>
              <Avatar name={idea.user.name} src={idea.user.avatar} size="sm" />
              <div className="text-sm">
                <p className="font-medium text-gray-900">{idea.user.name}</p>
                <p className="text-gray-500">{formatDate(idea.created_at)}</p>
              </div>
            </>
          )}
          {idea.is_anonymous && (
            <div className="text-sm">
              <p className="font-medium text-gray-900">Anonymous</p>
              <p className="text-gray-500">{formatDate(idea.created_at)}</p>
            </div>
          )}
        </div>

        <div className="flex items-center space-x-4 text-sm text-gray-500">
          <button
            onClick={handleLike}
            className="flex items-center space-x-1 hover:text-red-600 transition-colors"
          >
            {isLiked ? (
              <HeartSolidIcon className="h-5 w-5 text-red-600" />
            ) : (
              <HeartIcon className="h-5 w-5" />
            )}
            <span>{idea.likes_count || 0}</span>
          </button>
          <div className="flex items-center space-x-1">
            <ChatBubbleLeftIcon className="h-5 w-5" />
            <span>{idea.comments_count || 0}</span>
          </div>
          <div className="flex items-center space-x-1">
            <EyeIcon className="h-5 w-5" />
            <span>{idea.views_count || 0}</span>
          </div>
        </div>
      </div>
    </Link>
  );
};

export default IdeaCard;
