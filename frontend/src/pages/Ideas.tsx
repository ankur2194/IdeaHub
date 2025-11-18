import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { fetchIdeas, setFilters, likeIdea } from '../store/ideasSlice';
import { fetchCategories } from '../store/categoriesSlice';
import IdeaCard from '../components/ideas/IdeaCard';
import AdvancedFilters, { type FilterParams } from '../components/search/AdvancedFilters';
import { MagnifyingGlassIcon, FunnelIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useDebounce } from '../hooks/useDebounce';
import type { IdeaStatus } from '../types';

const Ideas = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const dispatch = useAppDispatch();
  const { ideas, loading, pagination, filters } = useAppSelector((state) => state.ideas);
  const { categories } = useAppSelector((state) => state.categories);

  const [searchQuery, setSearchQuery] = useState(searchParams.get('search') || '');
  const [showFilters, setShowFilters] = useState(false);

  // Debounce search query to avoid excessive API calls
  const debouncedSearchQuery = useDebounce(searchQuery, 500);

  useEffect(() => {
    dispatch(fetchCategories());
  }, [dispatch]);

  // Auto-search with debouncing when user types
  useEffect(() => {
    // Only trigger auto-search if there's a meaningful change
    const currentSearch = searchParams.get('search') || '';
    if (debouncedSearchQuery !== currentSearch) {
      if (debouncedSearchQuery.trim()) {
        setSearchParams({ ...Object.fromEntries(searchParams), search: debouncedSearchQuery });
      } else if (currentSearch) {
        // Clear search if query is empty
        const params = Object.fromEntries(searchParams);
        delete params.search;
        setSearchParams(params);
      }
    }
  }, [debouncedSearchQuery, searchParams, setSearchParams]);

  useEffect(() => {
    const categoryId = searchParams.get('category');
    const status = searchParams.get('status') as IdeaStatus | null;
    const search = searchParams.get('search');
    const userId = searchParams.get('user_id');
    const tags = searchParams.get('tags');
    const dateFrom = searchParams.get('date_from');
    const dateTo = searchParams.get('date_to');

    const newFilters = {
      ...filters,
      ...(categoryId && { category_id: parseInt(categoryId) }),
      ...(status && { status }),
      ...(search && { search }),
      ...(userId && { user_id: parseInt(userId) }),
      ...(tags && { tags: tags.split(',').map(Number) }),
      ...(dateFrom && { date_from: dateFrom }),
      ...(dateTo && { date_to: dateTo }),
    };

    dispatch(setFilters(newFilters));
    dispatch(fetchIdeas(newFilters));
  }, [searchParams, dispatch, filters]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      setSearchParams({ ...Object.fromEntries(searchParams), search: searchQuery });
    } else {
      searchParams.delete('search');
      setSearchParams(searchParams);
    }
  };

  const handleFilterChange = (newFilters: FilterParams) => {
    const params: Record<string, string> = {};

    // Preserve existing search query
    if (searchQuery) {
      params.search = searchQuery;
    }

    // Add new filter params
    if (newFilters.status) params.status = newFilters.status;
    if (newFilters.category_id) params.category = newFilters.category_id.toString();
    if (newFilters.user_id) params.user_id = newFilters.user_id.toString();
    if (newFilters.tags && newFilters.tags.length > 0) {
      params.tags = newFilters.tags.join(',');
    }
    if (newFilters.date_from) params.date_from = newFilters.date_from;
    if (newFilters.date_to) params.date_to = newFilters.date_to;

    setSearchParams(params);
  };

  const handleClearFilters = () => {
    setSearchParams({});
    setSearchQuery('');
  };

  const handleLike = (id: number) => {
    dispatch(likeIdea(id));
  };

  const handlePageChange = (page: number) => {
    dispatch(fetchIdeas({ ...filters, page }));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Browse Ideas</h1>
        <button
          onClick={() => setShowFilters(!showFilters)}
          className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          <FunnelIcon className="mr-2 h-5 w-5" />
          Filters
        </button>
      </div>

      {/* Search Bar */}
      <form onSubmit={handleSearch} className="flex gap-2">
        <div className="relative flex-1">
          <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search ideas by title or description..."
            className="block w-full rounded-md border-0 py-2 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
          />
          {searchQuery && (
            <button
              type="button"
              onClick={() => {
                setSearchQuery('');
                const params = Object.fromEntries(searchParams);
                delete params.search;
                setSearchParams(params);
              }}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          )}
        </div>
        <button
          type="submit"
          className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
        >
          Search
        </button>
      </form>

      {/* Sort By */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <label className="text-sm font-medium text-gray-700">Sort by:</label>
          <select
            value={filters.sort_by || 'created_at'}
            onChange={(e) => {
              const sortValue = e.target.value as 'created_at' | 'likes_count' | 'comments_count' | 'views_count' | 'title';
              dispatch(setFilters({ ...filters, sort_by: sortValue }));
              dispatch(fetchIdeas({ ...filters, sort_by: sortValue }));
            }}
            className="rounded-md border-0 py-1.5 pl-3 pr-10 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600"
          >
            <option value="created_at">Newest</option>
            <option value="likes_count">Most Liked</option>
            <option value="comments_count">Most Discussed</option>
            <option value="views_count">Most Viewed</option>
            <option value="title">Title (A-Z)</option>
          </select>
        </div>
      </div>

      {/* Advanced Filters Panel */}
      {showFilters && (
        <AdvancedFilters
          filters={{
            status: searchParams.get('status') || undefined,
            category_id: searchParams.get('category')
              ? parseInt(searchParams.get('category')!)
              : undefined,
            user_id: searchParams.get('user_id')
              ? parseInt(searchParams.get('user_id')!)
              : undefined,
            tags: searchParams.get('tags')
              ? searchParams.get('tags')!.split(',').map(Number)
              : undefined,
            date_from: searchParams.get('date_from') || undefined,
            date_to: searchParams.get('date_to') || undefined,
          }}
          categories={categories}
          onFilterChange={handleFilterChange}
          onClearFilters={handleClearFilters}
        />
      )}

      {/* Ideas Grid */}
      {loading ? (
        <div className="flex items-center justify-center py-12">
          <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-blue-600"></div>
        </div>
      ) : ideas.length > 0 ? (
        <>
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {ideas.map((idea) => (
              <IdeaCard key={idea.id} idea={idea} onLike={handleLike} />
            ))}
          </div>

          {/* Pagination */}
          {pagination && pagination.last_page > 1 && (
            <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
              <div className="flex flex-1 justify-between sm:hidden">
                <button
                  onClick={() => handlePageChange(pagination.current_page - 1)}
                  disabled={pagination.current_page === 1}
                  className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                  Previous
                </button>
                <button
                  onClick={() => handlePageChange(pagination.current_page + 1)}
                  disabled={pagination.current_page === pagination.last_page}
                  className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
              <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                  <p className="text-sm text-gray-700">
                    Showing page <span className="font-medium">{pagination.current_page}</span> of{' '}
                    <span className="font-medium">{pagination.last_page}</span>
                  </p>
                </div>
                <div>
                  <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <button
                      onClick={() => handlePageChange(pagination.current_page - 1)}
                      disabled={pagination.current_page === 1}
                      className="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Previous
                    </button>
                    <button
                      onClick={() => handlePageChange(pagination.current_page + 1)}
                      disabled={pagination.current_page === pagination.last_page}
                      className="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                    >
                      Next
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          )}
        </>
      ) : (
        <div className="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
          <p className="text-sm text-gray-500">No ideas found matching your criteria.</p>
        </div>
      )}
    </div>
  );
};

export default Ideas;
