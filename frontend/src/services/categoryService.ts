import api from './api';
import type { ApiResponse, Category } from '../types';

export const categoryService = {
  /**
   * Get all categories
   */
  getCategories: async (): Promise<ApiResponse<Category[]>> => {
    const response = await api.get('/categories');
    return response.data;
  },

  /**
   * Get a single category by ID
   */
  getCategory: async (id: number): Promise<ApiResponse<Category>> => {
    const response = await api.get(`/categories/${id}`);
    return response.data;
  },

  /**
   * Create a new category (admin only)
   */
  createCategory: async (data: Partial<Category>): Promise<ApiResponse<Category>> => {
    const response = await api.post('/categories', data);
    return response.data;
  },

  /**
   * Update a category (admin only)
   */
  updateCategory: async (id: number, data: Partial<Category>): Promise<ApiResponse<Category>> => {
    const response = await api.put(`/categories/${id}`, data);
    return response.data;
  },

  /**
   * Delete a category (admin only)
   */
  deleteCategory: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/categories/${id}`);
    return response.data;
  },
};
