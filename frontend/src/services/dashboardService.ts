import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export interface DashboardWidget {
  id: string;
  type: string;
  title: string;
  config: Record<string, any>;
  x: number;
  y: number;
  w: number;
  h: number;
}

export interface Dashboard {
  id: number;
  user_id: number;
  name: string;
  is_default: boolean;
  is_shared: boolean;
  layout: DashboardWidget[];
  created_at: string;
  updated_at: string;
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return {
    Authorization: `Bearer ${token}`,
  };
};

export const getDashboards = async (): Promise<Dashboard[]> => {
  const response = await axios.get(`${API_BASE_URL}/api/dashboards`, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const getDashboard = async (id: number): Promise<Dashboard> => {
  const response = await axios.get(`${API_BASE_URL}/api/dashboards/${id}`, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const createDashboard = async (
  name: string,
  layout: DashboardWidget[],
  isDefault: boolean = false
): Promise<Dashboard> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/dashboards`,
    {
      name,
      layout,
      is_default: isDefault,
    },
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data.data;
};

export const updateDashboard = async (
  id: number,
  updates: Partial<Dashboard>
): Promise<Dashboard> => {
  const response = await axios.put(`${API_BASE_URL}/api/dashboards/${id}`, updates, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const deleteDashboard = async (id: number): Promise<void> => {
  await axios.delete(`${API_BASE_URL}/api/dashboards/${id}`, {
    headers: getAuthHeaders(),
  });
};

export const setDefaultDashboard = async (id: number): Promise<Dashboard> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/dashboards/${id}/set-default`,
    {},
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data.data;
};

export const shareDashboard = async (id: number): Promise<Dashboard> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/dashboards/${id}/share`,
    {},
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data.data;
};

export default {
  getDashboards,
  getDashboard,
  createDashboard,
  updateDashboard,
  deleteDashboard,
  setDefaultDashboard,
  shareDashboard,
};
