import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { createIdea } from '../store/ideasSlice';
import { fetchCategories } from '../store/categoriesSlice';
import { fetchTags } from '../store/tagsSlice';

const CreateIdea = () => {
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { loading, error } = useAppSelector((state) => state.ideas);
  const { categories } = useAppSelector((state) => state.categories);
  const { tags } = useAppSelector((state) => state.tags);

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    category_id: '',
    is_anonymous: false,
    tags: [] as number[],
  });

  useEffect(() => {
    dispatch(fetchCategories());
    dispatch(fetchTags());
  }, [dispatch]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value, type } = e.target;
    setFormData({
      ...formData,
      [name]: type === 'checkbox' ? (e.target as HTMLInputElement).checked : value,
    });
  };

  const handleTagToggle = (tagId: number) => {
    setFormData({
      ...formData,
      tags: formData.tags.includes(tagId)
        ? formData.tags.filter((id) => id !== tagId)
        : [...formData.tags, tagId],
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const ideaData = {
        ...formData,
        category_id: formData.category_id ? parseInt(formData.category_id) : undefined,
      };
      await dispatch(createIdea(ideaData)).unwrap();
      navigate('/ideas');
    } catch (err) {
      // Error is handled by Redux slice
    }
  };

  return (
    <div className="mx-auto max-w-3xl">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900">Create New Idea</h1>
        <p className="mt-2 text-sm text-gray-600">
          Share your innovative idea with the team. Your idea will be saved as a draft until
          you're ready to submit it for review.
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        {error && (
          <div className="rounded-md bg-red-50 p-4">
            <p className="text-sm text-red-800">{error}</p>
          </div>
        )}

        <div>
          <label htmlFor="title" className="block text-sm font-medium text-gray-700">
            Idea Title *
          </label>
          <input
            type="text"
            name="title"
            id="title"
            required
            value={formData.title}
            onChange={handleChange}
            className="mt-1 block w-full rounded-md border-0 px-3 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
            placeholder="Enter a descriptive title for your idea"
          />
        </div>

        <div>
          <label htmlFor="description" className="block text-sm font-medium text-gray-700">
            Description *
          </label>
          <textarea
            name="description"
            id="description"
            required
            rows={6}
            value={formData.description}
            onChange={handleChange}
            className="mt-1 block w-full rounded-md border-0 px-3 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
            placeholder="Describe your idea in detail. What problem does it solve? How would it work?"
          />
        </div>

        <div>
          <label htmlFor="category_id" className="block text-sm font-medium text-gray-700">
            Category
          </label>
          <select
            name="category_id"
            id="category_id"
            value={formData.category_id}
            onChange={handleChange}
            className="mt-1 block w-full rounded-md border-0 px-3 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
          >
            <option value="">Select a category</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700">Tags</label>
          <div className="mt-2 flex flex-wrap gap-2">
            {tags.map((tag) => (
              <button
                key={tag.id}
                type="button"
                onClick={() => handleTagToggle(tag.id)}
                className={`inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium transition-colors ${
                  formData.tags.includes(tag.id)
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                {tag.name}
              </button>
            ))}
          </div>
        </div>

        <div className="flex items-center">
          <input
            type="checkbox"
            name="is_anonymous"
            id="is_anonymous"
            checked={formData.is_anonymous}
            onChange={handleChange}
            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
          />
          <label htmlFor="is_anonymous" className="ml-2 block text-sm text-gray-700">
            Submit this idea anonymously
          </label>
        </div>

        <div className="flex items-center justify-end space-x-4 border-t border-gray-200 pt-6">
          <button
            type="button"
            onClick={() => navigate(-1)}
            className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Creating...' : 'Create Idea'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default CreateIdea;
