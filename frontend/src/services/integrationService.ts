import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export interface Integration {
  id: number;
  tenant_id: number;
  type: string;
  name: string;
  config: Record<string, any>;
  is_active: boolean;
  last_synced_at?: string;
  created_at: string;
  updated_at: string;
  logs?: IntegrationLog[];
}

export interface IntegrationLog {
  id: number;
  integration_id: number;
  action: string;
  status: 'success' | 'failed';
  payload?: Record<string, any>;
  error_message?: string;
  created_at: string;
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return {
    Authorization: `Bearer ${token}`,
  };
};

export const getIntegrations = async (type?: string): Promise<Integration[]> => {
  const params = type ? { type } : {};
  const response = await axios.get(`${API_BASE_URL}/api/integrations`, {
    params,
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const getIntegration = async (id: number): Promise<Integration> => {
  const response = await axios.get(`${API_BASE_URL}/api/integrations/${id}`, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const createIntegration = async (
  name: string,
  type: string,
  config: Record<string, any>,
  isActive: boolean = true
): Promise<Integration> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/integrations`,
    {
      name,
      type,
      config,
      is_active: isActive,
    },
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data.data;
};

export const updateIntegration = async (
  id: number,
  updates: Partial<Integration>
): Promise<Integration> => {
  const response = await axios.put(`${API_BASE_URL}/api/integrations/${id}`, updates, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const deleteIntegration = async (id: number): Promise<void> => {
  await axios.delete(`${API_BASE_URL}/api/integrations/${id}`, {
    headers: getAuthHeaders(),
  });
};

export const testIntegration = async (id: number): Promise<any> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/integrations/${id}/test`,
    {},
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data;
};

export const syncIntegration = async (id: number): Promise<any> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/integrations/${id}/sync`,
    {},
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data;
};

export default {
  getIntegrations,
  getIntegration,
  createIntegration,
  updateIntegration,
  deleteIntegration,
  testIntegration,
  syncIntegration,
};
