import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export interface TenantBranding {
  id: number;
  tenant_id: number;
  logo_url?: string;
  logo_dark_url?: string;
  favicon_url?: string;
  primary_color: string;
  secondary_color: string;
  accent_color: string;
  font_family: string;
  font_heading: string;
  custom_css?: string;
  login_background_url?: string;
  company_name?: string;
  tagline?: string;
  footer_text?: string;
  social_links?: {
    twitter?: string;
    linkedin?: string;
    facebook?: string;
    github?: string;
  };
  created_at: string;
  updated_at: string;
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return {
    Authorization: `Bearer ${token}`,
  };
};

export const getBranding = async (): Promise<TenantBranding> => {
  const response = await axios.get(`${API_BASE_URL}/api/branding`, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const updateBranding = async (
  branding: Partial<TenantBranding>
): Promise<TenantBranding> => {
  const response = await axios.put(`${API_BASE_URL}/api/branding`, branding, {
    headers: getAuthHeaders(),
  });
  return response.data.data;
};

export const uploadLogo = async (
  file: File,
  type: 'logo' | 'logo_dark' | 'favicon' | 'login_background'
): Promise<{ url: string }> => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', type);

  const response = await axios.post(`${API_BASE_URL}/api/branding/upload-logo`, formData, {
    headers: {
      ...getAuthHeaders(),
      'Content-Type': 'multipart/form-data',
    },
  });
  return response.data.data;
};

export const deleteLogo = async (
  type: 'logo' | 'logo_dark' | 'favicon' | 'login_background'
): Promise<void> => {
  await axios.delete(`${API_BASE_URL}/api/branding/logo/${type}`, {
    headers: getAuthHeaders(),
  });
};

export const resetBranding = async (): Promise<TenantBranding> => {
  const response = await axios.post(
    `${API_BASE_URL}/api/branding/reset`,
    {},
    {
      headers: getAuthHeaders(),
    }
  );
  return response.data.data;
};

export const getCSSVariables = async (): Promise<string> => {
  const response = await axios.get(`${API_BASE_URL}/api/branding/css`, {
    headers: getAuthHeaders(),
  });
  return response.data.data.css;
};

export default {
  getBranding,
  updateBranding,
  uploadLogo,
  deleteLogo,
  resetBranding,
  getCSSVariables,
};
