import type { IdeaStatus } from '../../types';
import { getStatusColor, getStatusLabel } from '../../utils/statusHelpers';

interface StatusBadgeProps {
  status: IdeaStatus;
  className?: string;
}

const StatusBadge: React.FC<StatusBadgeProps> = ({ status, className = '' }) => {
  return (
    <span
      className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getStatusColor(
        status
      )} ${className}`}
    >
      {getStatusLabel(status)}
    </span>
  );
};

export default StatusBadge;
