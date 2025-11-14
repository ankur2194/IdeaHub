import { getInitials } from '../../utils/formatters';

interface AvatarProps {
  name: string;
  src?: string | null;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const Avatar: React.FC<AvatarProps> = ({ name, src, size = 'md', className = '' }) => {
  const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-12 w-12 text-base',
  };

  if (src) {
    return (
      <img
        src={src}
        alt={name}
        className={`inline-block rounded-full ${sizeClasses[size]} ${className}`}
      />
    );
  }

  return (
    <span
      className={`inline-flex items-center justify-center rounded-full bg-blue-600 font-medium text-white ${sizeClasses[size]} ${className}`}
    >
      {getInitials(name)}
    </span>
  );
};

export default Avatar;
