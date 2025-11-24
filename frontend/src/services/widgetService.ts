import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export interface Widget {
  id: number;
  type: string;
  category: string;
  name: string;
  description: string;
  default_config: Record<string, any>;
  default_size: { w: number; h: number };
  preview_data?: any;
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return {
    Authorization: `Bearer ${token}`,
  };
};

export const getAvailableWidgets = async (): Promise<Widget[]> => {
  const response = await axios.get(`${API_BASE_URL}/api/widgets`, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const getWidget = async (id: number): Promise<Widget> => {
  const response = await axios.get(`${API_BASE_URL}/api/widgets/${id}`, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const getWidgetPreview = async (
  type: string,
  config?: Record<string, any>
): Promise<any> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/widgets/preview`,
    { type, config },
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data.data;
};

export default {
  getAvailableWidgets,
  getWidget,
  getWidgetPreview,
};
