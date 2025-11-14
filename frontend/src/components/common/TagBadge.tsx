import type { Tag } from '../../types';

interface TagBadgeProps {
  tag: Tag;
  className?: string;
}

const TagBadge: React.FC<TagBadgeProps> = ({ tag, className = '' }) => {
  return (
    <span
      className={`inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${className}`}
      style={{
        backgroundColor: `${tag.color}10`,
        color: tag.color,
        borderColor: `${tag.color}30`,
      }}
    >
      {tag.name}
    </span>
  );
};

export default TagBadge;
