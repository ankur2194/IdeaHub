import api from './api';
import type { ApiResponse, CreateIdeaData, Idea, IdeaFilters, PaginatedResponse, UpdateIdeaData } from '../types';

export const ideaService = {
  /**
   * Get paginated list of ideas
   */
  getIdeas: async (filters?: IdeaFilters): Promise<ApiResponse<PaginatedResponse<Idea>>> => {
    const response = await api.get('/ideas', { params: filters });
    return response.data;
  },

  /**
   * Get a single idea by ID
   */
  getIdea: async (id: number): Promise<ApiResponse<Idea>> => {
    const response = await api.get(`/ideas/${id}`);
    return response.data;
  },

  /**
   * Create a new idea
   */
  createIdea: async (data: CreateIdeaData | FormData): Promise<ApiResponse<Idea>> => {
    const config = data instanceof FormData ? {
      headers: { 'Content-Type': 'multipart/form-data' }
    } : {};
    const response = await api.post('/ideas', data, config);
    return response.data;
  },

  /**
   * Update an existing idea
   */
  updateIdea: async (id: number, data: UpdateIdeaData): Promise<ApiResponse<Idea>> => {
    const response = await api.put(`/ideas/${id}`, data);
    return response.data;
  },

  /**
   * Delete an idea
   */
  deleteIdea: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/ideas/${id}`);
    return response.data;
  },

  /**
   * Submit an idea for review
   */
  submitIdea: async (id: number): Promise<ApiResponse<Idea>> => {
    const response = await api.post(`/ideas/${id}/submit`);
    return response.data;
  },

  /**
   * Like an idea
   */
  likeIdea: async (id: number): Promise<ApiResponse<{ liked: boolean; likes_count: number }>> => {
    const response = await api.post(`/ideas/${id}/like`);
    return response.data;
  },
};
