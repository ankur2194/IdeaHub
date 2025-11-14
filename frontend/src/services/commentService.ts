import api from './api';
import type { ApiResponse, Comment, CreateCommentData } from '../types';

export const commentService = {
  /**
   * Get comments for an idea
   */
  getComments: async (ideaId: number): Promise<ApiResponse<Comment[]>> => {
    const response = await api.get(`/ideas/${ideaId}/comments`);
    return response.data;
  },

  /**
   * Get a single comment
   */
  getComment: async (id: number): Promise<ApiResponse<Comment>> => {
    const response = await api.get(`/comments/${id}`);
    return response.data;
  },

  /**
   * Create a new comment
   */
  createComment: async (data: CreateCommentData): Promise<ApiResponse<Comment>> => {
    const response = await api.post('/comments', data);
    return response.data;
  },

  /**
   * Update a comment
   */
  updateComment: async (id: number, content: string): Promise<ApiResponse<Comment>> => {
    const response = await api.put(`/comments/${id}`, { content });
    return response.data;
  },

  /**
   * Delete a comment
   */
  deleteComment: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/comments/${id}`);
    return response.data;
  },

  /**
   * Like a comment
   */
  likeComment: async (id: number): Promise<ApiResponse<{ liked: boolean; likes_count: number }>> => {
    const response = await api.post(`/comments/${id}/like`);
    return response.data;
  },
};
