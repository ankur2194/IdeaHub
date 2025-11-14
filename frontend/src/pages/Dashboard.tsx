import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchIdeas, likeIdea } from '../store/ideasSlice';
import { fetchCategories } from '../store/categoriesSlice';
import IdeaCard from '../components/ideas/IdeaCard';
import {
  LightBulbIcon,
  ClockIcon,
  CheckCircleIcon,
  ChartBarIcon,
} from '@heroicons/react/24/outline';

const Dashboard = () => {
  const dispatch = useAppDispatch();
  const { ideas, loading } = useAppSelector((state) => state.ideas);
  const { categories } = useAppSelector((state) => state.categories);
  const { user } = useAppSelector((state) => state.auth);

  useEffect(() => {
    dispatch(fetchIdeas({ per_page: 6, sort_by: 'created_at', sort_order: 'desc' }));
    dispatch(fetchCategories());
  }, [dispatch]);

  const handleLike = (id: number) => {
    dispatch(likeIdea(id));
  };

  // Calculate stats
  const recentIdeas = ideas.slice(0, 6);
  const stats = [
    {
      name: 'Total Ideas',
      value: '0',
      icon: LightBulbIcon,
      color: 'bg-blue-500',
    },
    {
      name: 'Pending Review',
      value: '0',
      icon: ClockIcon,
      color: 'bg-yellow-500',
    },
    {
      name: 'Approved',
      value: '0',
      icon: CheckCircleIcon,
      color: 'bg-green-500',
    },
    {
      name: 'Implemented',
      value: '0',
      icon: ChartBarIcon,
      color: 'bg-purple-500',
    },
  ];

  return (
    <div className="space-y-8">
      {/* Welcome Section */}
      <div className="rounded-lg bg-white p-6 shadow-sm">
        <h1 className="text-2xl font-bold text-gray-900">
          Welcome back, {user?.name}!
        </h1>
        <p className="mt-2 text-gray-600">
          Share your innovative ideas and help shape the future of our organization.
        </p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <div
            key={stat.name}
            className="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-sm sm:p-6"
          >
            <div className="flex items-center">
              <div className={`rounded-md p-3 ${stat.color}`}>
                <stat.icon className="h-6 w-6 text-white" />
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="truncate text-sm font-medium text-gray-500">
                    {stat.name}
                  </dt>
                  <dd className="text-lg font-semibold text-gray-900">{stat.value}</dd>
                </dl>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Categories Section */}
      <div>
        <h2 className="mb-4 text-lg font-semibold text-gray-900">Categories</h2>
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          {categories.slice(0, 8).map((category) => (
            <Link
              key={category.id}
              to={`/ideas?category=${category.id}`}
              className="flex items-center space-x-3 rounded-lg border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md"
            >
              <div
                className="flex h-10 w-10 items-center justify-center rounded-lg"
                style={{ backgroundColor: `${category.color}20` }}
              >
                <span className="text-xl">{category.icon}</span>
              </div>
              <div className="flex-1 min-w-0">
                <p className="truncate text-sm font-medium text-gray-900">
                  {category.name}
                </p>
                <p className="text-xs text-gray-500">
                  {category.ideas_count || 0} ideas
                </p>
              </div>
            </Link>
          ))}
        </div>
      </div>

      {/* Recent Ideas Section */}
      <div>
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">Recent Ideas</h2>
          <Link
            to="/ideas"
            className="text-sm font-medium text-blue-600 hover:text-blue-500"
          >
            View all
          </Link>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-blue-600"></div>
          </div>
        ) : recentIdeas.length > 0 ? (
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {recentIdeas.map((idea) => (
              <IdeaCard key={idea.id} idea={idea} onLike={handleLike} />
            ))}
          </div>
        ) : (
          <div className="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
            <LightBulbIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-semibold text-gray-900">No ideas yet</h3>
            <p className="mt-1 text-sm text-gray-500">
              Get started by creating a new idea.
            </p>
            <div className="mt-6">
              <Link
                to="/ideas/create"
                className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
              >
                Create Idea
              </Link>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Dashboard;
