import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../store/hooks';
import { fetchTags } from '../../store/tagsSlice';
import { XMarkIcon } from '@heroicons/react/24/outline';
import type { Category } from '../../types';

export interface FilterParams {
  status?: string;
  category_id?: number;
  user_id?: number;
  tags?: number[];
  date_from?: string;
  date_to?: string;
}

interface AdvancedFiltersProps {
  filters: FilterParams;
  categories: Category[];
  onFilterChange: (filters: FilterParams) => void;
  onClearFilters: () => void;
}

const AdvancedFilters: React.FC<AdvancedFiltersProps> = ({
  filters,
  categories,
  onFilterChange,
  onClearFilters,
}) => {
  const dispatch = useAppDispatch();
  const { tags } = useAppSelector((state) => state.tags);

  // Derive state from props instead of using local state
  const selectedTags = filters.tags || [];
  const dateFrom = filters.date_from || '';
  const dateTo = filters.date_to || '';

  useEffect(() => {
    dispatch(fetchTags());
  }, [dispatch]);

  const handleStatusChange = (status: string) => {
    onFilterChange({ ...filters, status: status || undefined });
  };

  const handleCategoryChange = (categoryId: string) => {
    onFilterChange({
      ...filters,
      category_id: categoryId ? parseInt(categoryId) : undefined,
    });
  };

  const handleTagToggle = (tagId: number) => {
    const newTags = selectedTags.includes(tagId)
      ? selectedTags.filter((id) => id !== tagId)
      : [...selectedTags, tagId];

    onFilterChange({
      ...filters,
      tags: newTags.length > 0 ? newTags : undefined,
    });
  };

  const handleDateFromChange = (date: string) => {
    onFilterChange({
      ...filters,
      date_from: date || undefined,
    });
  };

  const handleDateToChange = (date: string) => {
    onFilterChange({
      ...filters,
      date_to: date || undefined,
    });
  };

  const hasActiveFilters =
    filters.status ||
    filters.category_id ||
    (filters.tags && filters.tags.length > 0) ||
    filters.date_from ||
    filters.date_to;

  return (
    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <div className="mb-4 flex items-center justify-between">
        <h3 className="text-lg font-semibold text-gray-900">Advanced Filters</h3>
        {hasActiveFilters && (
          <button
            onClick={onClearFilters}
            className="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700"
          >
            <XMarkIcon className="h-4 w-4" />
            Clear All
          </button>
        )}
      </div>

      <div className="space-y-4">
        {/* Row 1: Status and Category */}
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label className="block text-sm font-medium text-gray-700">Status</label>
            <select
              value={filters.status || ''}
              onChange={(e) => handleStatusChange(e.target.value)}
              className="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="submitted">Submitted</option>
              <option value="under_review">Under Review</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="implemented">Implemented</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Category</label>
            <select
              value={filters.category_id || ''}
              onChange={(e) => handleCategoryChange(e.target.value)}
              className="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
            >
              <option value="">All Categories</option>
              {categories.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Row 2: Date Range */}
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label className="block text-sm font-medium text-gray-700">Date From</label>
            <input
              type="date"
              value={dateFrom}
              onChange={(e) => handleDateFromChange(e.target.value)}
              max={dateTo || undefined}
              className="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Date To</label>
            <input
              type="date"
              value={dateTo}
              onChange={(e) => handleDateToChange(e.target.value)}
              min={dateFrom || undefined}
              className="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
            />
          </div>
        </div>

        {/* Row 3: Tags */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">Tags</label>
          {tags.length === 0 ? (
            <p className="text-sm text-gray-500">No tags available</p>
          ) : (
            <div className="flex flex-wrap gap-2">
              {tags.map((tag) => (
                <button
                  key={tag.id}
                  onClick={() => handleTagToggle(tag.id)}
                  className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium transition-colors ${
                    selectedTags.includes(tag.id)
                      ? 'bg-blue-600 text-white hover:bg-blue-700'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  }`}
                >
                  {tag.name}
                  {selectedTags.includes(tag.id) && (
                    <XMarkIcon className="h-4 w-4" />
                  )}
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Active Filters Summary */}
        {hasActiveFilters && (
          <div className="border-t border-gray-200 pt-4">
            <p className="text-sm font-medium text-gray-700 mb-2">Active Filters:</p>
            <div className="flex flex-wrap gap-2">
              {filters.status && (
                <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                  Status: {filters.status}
                  <button
                    onClick={() => handleStatusChange('')}
                    className="hover:text-blue-900"
                  >
                    <XMarkIcon className="h-3 w-3" />
                  </button>
                </span>
              )}
              {filters.category_id && (
                <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                  Category:{' '}
                  {categories.find((c) => c.id === filters.category_id)?.name}
                  <button
                    onClick={() => handleCategoryChange('')}
                    className="hover:text-blue-900"
                  >
                    <XMarkIcon className="h-3 w-3" />
                  </button>
                </span>
              )}
              {selectedTags.length > 0 && (
                <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                  {selectedTags.length} tag{selectedTags.length > 1 ? 's' : ''} selected
                </span>
              )}
              {filters.date_from && (
                <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                  From: {filters.date_from}
                  <button
                    onClick={() => handleDateFromChange('')}
                    className="hover:text-blue-900"
                  >
                    <XMarkIcon className="h-3 w-3" />
                  </button>
                </span>
              )}
              {filters.date_to && (
                <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                  To: {filters.date_to}
                  <button
                    onClick={() => handleDateToChange('')}
                    className="hover:text-blue-900"
                  >
                    <XMarkIcon className="h-3 w-3" />
                  </button>
                </span>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AdvancedFilters;
