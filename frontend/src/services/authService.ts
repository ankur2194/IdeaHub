import api from './api';
import type { ApiResponse, LoginCredentials, RegisterData, User } from '../types';

export const authService = {
  /**
   * Register a new user
   */
  register: async (data: RegisterData): Promise<ApiResponse<{ user: User; token: string }>> => {
    const response = await api.post('/register', data);
    return response.data;
  },

  /**
   * Login user
   */
  login: async (credentials: LoginCredentials): Promise<ApiResponse<{ user: User; token: string }>> => {
    const response = await api.post('/login', credentials);
    return response.data;
  },

  /**
   * Logout user
   */
  logout: async (): Promise<ApiResponse<null>> => {
    const response = await api.post('/logout');
    return response.data;
  },

  /**
   * Get authenticated user
   */
  getUser: async (): Promise<ApiResponse<User>> => {
    const response = await api.get('/user');
    return response.data;
  },
};
