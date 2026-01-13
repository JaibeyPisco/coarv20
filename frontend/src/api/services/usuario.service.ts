import apiClient from '../axios';
import type { Usuario } from '@/types/configuracion';
import type { ChangePasswordDto } from '@/types/configuracion';

export const usuarioService = {
  getAll: async (): Promise<Usuario[]> => {
    const response = await apiClient.get<Usuario[]>('/configuracion/usuario');
    return response.data;
  },

  getById: async (id: number): Promise<Usuario> => {
    const response = await apiClient.get<Usuario>(`/configuracion/usuario/${id}`);
    return response.data;
  },

  create: async (data: FormData): Promise<Usuario> => {
    const response = await apiClient.post<Usuario>('/configuracion/usuario', data);
    return response.data;
  },

  update: async (id: number, data: FormData): Promise<Usuario> => {
    data.append('id', id.toString());
    const response = await apiClient.post<Usuario>('/configuracion/usuario', data);
    return response.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/configuracion/usuario/${id}`);
  },

  changePassword: async (id: number, data: ChangePasswordDto): Promise<void> => {
    await apiClient.post(`/configuracion/usuario/${id}/change-password`, data);
  },

  toggleSuspend: async (id: number): Promise<Usuario> => {
    const response = await apiClient.post<Usuario>(`/configuracion/usuario/${id}/suspend`);
    return response.data;
  },
};


