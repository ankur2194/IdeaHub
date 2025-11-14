import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchIdeas, likeIdea } from '../store/ideasSlice';
import IdeaCard from '../components/ideas/IdeaCard';
import { LightBulbIcon, PlusIcon } from '@heroicons/react/24/outline';

const MyIdeas = () => {
  const dispatch = useAppDispatch();
  const { ideas, loading } = useAppSelector((state) => state.ideas);
  const { user } = useAppSelector((state) => state.auth);

  useEffect(() => {
    // In a real implementation, you would filter by user_id on the backend
    // For now, we'll fetch all ideas and filter client-side
    dispatch(fetchIdeas({ per_page: 50, sort_by: 'created_at', sort_order: 'desc' }));
  }, [dispatch]);

  const handleLike = (id: number) => {
    dispatch(likeIdea(id));
  };

  // Filter ideas to only show current user's ideas
  const myIdeas = ideas.filter((idea) => idea.user_id === user?.id);

  // Group ideas by status
  const draftIdeas = myIdeas.filter((idea) => idea.status === 'draft');
  const submittedIdeas = myIdeas.filter((idea) =>
    ['submitted', 'under_review'].includes(idea.status)
  );
  const approvedIdeas = myIdeas.filter((idea) => idea.status === 'approved');
  const implementedIdeas = myIdeas.filter((idea) => idea.status === 'implemented');
  const rejectedIdeas = myIdeas.filter((idea) => idea.status === 'rejected');

  const renderIdeaGroup = (title: string, ideas: typeof myIdeas) => {
    if (ideas.length === 0) return null;

    return (
      <div className="mb-8">
        <h2 className="mb-4 text-lg font-semibold text-gray-900">
          {title} ({ideas.length})
        </h2>
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {ideas.map((idea) => (
            <IdeaCard key={idea.id} idea={idea} onLike={handleLike} />
          ))}
        </div>
      </div>
    );
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">My Ideas</h1>
          <p className="mt-1 text-sm text-gray-600">
            Manage and track your submitted ideas
          </p>
        </div>
        <Link
          to="/ideas/create"
          className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
        >
          <PlusIcon className="mr-2 h-5 w-5" />
          New Idea
        </Link>
      </div>

      {/* Stats cards */}
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div className="rounded-lg border border-gray-200 bg-white p-4">
          <div className="text-2xl font-bold text-gray-900">{myIdeas.length}</div>
          <div className="text-sm text-gray-600">Total</div>
        </div>
        <div className="rounded-lg border border-gray-200 bg-white p-4">
          <div className="text-2xl font-bold text-yellow-600">{draftIdeas.length}</div>
          <div className="text-sm text-gray-600">Drafts</div>
        </div>
        <div className="rounded-lg border border-gray-200 bg-white p-4">
          <div className="text-2xl font-bold text-blue-600">{submittedIdeas.length}</div>
          <div className="text-sm text-gray-600">In Review</div>
        </div>
        <div className="rounded-lg border border-gray-200 bg-white p-4">
          <div className="text-2xl font-bold text-green-600">{approvedIdeas.length}</div>
          <div className="text-sm text-gray-600">Approved</div>
        </div>
        <div className="rounded-lg border border-gray-200 bg-white p-4">
          <div className="text-2xl font-bold text-purple-600">{implementedIdeas.length}</div>
          <div className="text-sm text-gray-600">Implemented</div>
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center py-12">
          <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-blue-600"></div>
        </div>
      ) : myIdeas.length === 0 ? (
        <div className="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
          <LightBulbIcon className="mx-auto h-12 w-12 text-gray-400" />
          <h3 className="mt-2 text-sm font-semibold text-gray-900">No ideas yet</h3>
          <p className="mt-1 text-sm text-gray-500">
            Get started by creating your first idea.
          </p>
          <div className="mt-6">
            <Link
              to="/ideas/create"
              className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
            >
              <PlusIcon className="mr-2 h-5 w-5" />
              Create Idea
            </Link>
          </div>
        </div>
      ) : (
        <div>
          {renderIdeaGroup('Drafts', draftIdeas)}
          {renderIdeaGroup('In Review', submittedIdeas)}
          {renderIdeaGroup('Approved', approvedIdeas)}
          {renderIdeaGroup('Implemented', implementedIdeas)}
          {renderIdeaGroup('Rejected', rejectedIdeas)}
        </div>
      )}
    </div>
  );
};

export default MyIdeas;
