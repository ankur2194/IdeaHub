import { Link, useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../store/hooks';
import { logout } from '../../store/authSlice';
import Avatar from '../common/Avatar';
import {
  Bars3Icon,
  BellIcon,
  PlusIcon,
} from '@heroicons/react/24/outline';

interface NavbarProps {
  onMenuClick?: () => void;
}

const Navbar: React.FC<NavbarProps> = ({ onMenuClick }) => {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { user } = useAppSelector((state) => state.auth);

  const handleLogout = async () => {
    await dispatch(logout());
    navigate('/login');
  };

  return (
    <nav className="sticky top-0 z-50 border-b border-gray-200 bg-white">
      <div className="mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          <div className="flex items-center">
            <button
              type="button"
              className="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:hidden"
              onClick={onMenuClick}
            >
              <Bars3Icon className="h-6 w-6" />
            </button>
            <Link to="/dashboard" className="ml-4 flex items-center lg:ml-0">
              <span className="text-2xl font-bold text-blue-600">IdeaHub</span>
            </Link>
          </div>

          <div className="hidden lg:ml-6 lg:flex lg:space-x-8">
            <Link
              to="/dashboard"
              className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-900 hover:border-gray-300"
            >
              Dashboard
            </Link>
            <Link
              to="/ideas"
              className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
            >
              Browse Ideas
            </Link>
            <Link
              to="/ideas/my"
              className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
            >
              My Ideas
            </Link>
          </div>

          <div className="flex items-center space-x-4">
            <Link
              to="/ideas/create"
              className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              <PlusIcon className="mr-2 h-5 w-5" />
              New Idea
            </Link>

            <button
              type="button"
              className="rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              <BellIcon className="h-6 w-6" />
            </button>

            <div className="relative">
              <button
                type="button"
                className="flex items-center space-x-3 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                {user && (
                  <>
                    <Avatar name={user.name} src={user.avatar} size="md" />
                    <div className="hidden text-left lg:block">
                      <p className="text-sm font-medium text-gray-700">{user.name}</p>
                      <p className="text-xs text-gray-500">{user.role}</p>
                    </div>
                  </>
                )}
              </button>
            </div>

            <button
              onClick={handleLogout}
              className="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
