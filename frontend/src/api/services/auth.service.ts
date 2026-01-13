import apiClient from '../axios';
import type { LoginDto, LoginResponse, UserResponse } from '@/types/auth';

export const authService = {
  login: async (credentials: LoginDto): Promise<LoginResponse> => {
    const response = await apiClient.post<LoginResponse>('/login', credentials);
    return response.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post('/logout');
  },

  getCurrentUser: async (): Promise<UserResponse> => {
    const response = await apiClient.get<UserResponse>('/user');
    return response.data;
  },

  checkAuth: async (): Promise<UserResponse | null> => {
    try {
      const response = await apiClient.get<UserResponse>('/user');
      return response.data;
    } catch {
      return null;
    }
  },
};


