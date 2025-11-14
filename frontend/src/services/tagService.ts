import api from './api';
import type { ApiResponse, Tag } from '../types';

export const tagService = {
  /**
   * Get all tags
   */
  getTags: async (): Promise<ApiResponse<Tag[]>> => {
    const response = await api.get('/tags');
    return response.data;
  },

  /**
   * Get a single tag by ID
   */
  getTag: async (id: number): Promise<ApiResponse<Tag>> => {
    const response = await api.get(`/tags/${id}`);
    return response.data;
  },

  /**
   * Create a new tag
   */
  createTag: async (data: Partial<Tag>): Promise<ApiResponse<Tag>> => {
    const response = await api.post('/tags', data);
    return response.data;
  },

  /**
   * Update a tag
   */
  updateTag: async (id: number, data: Partial<Tag>): Promise<ApiResponse<Tag>> => {
    const response = await api.put(`/tags/${id}`, data);
    return response.data;
  },

  /**
   * Delete a tag
   */
  deleteTag: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/tags/${id}`);
    return response.data;
  },
};
