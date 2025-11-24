import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export interface SSOProvider {
  id: number;
  tenant_id: number;
  provider_type: 'saml' | 'oauth' | 'oidc' | 'ldap';
  provider_name: string;
  is_enabled: boolean;
  is_default: boolean;
  config: {
    // SAML
    entity_id?: string;
    sso_url?: string;
    certificate?: string;
    // OAuth/OIDC
    client_id?: string;
    client_secret?: string;
    authorization_url?: string;
    token_url?: string;
    user_info_url?: string;
    // LDAP
    host?: string;
    port?: number;
    base_dn?: string;
    bind_dn?: string;
    bind_password?: string;
    // Common
    redirect_url?: string;
    scopes?: string[];
    auto_provision?: boolean;
    attribute_mapping?: Record<string, string>;
  };
  created_at: string;
  updated_at: string;
}

export interface SSOProviderListItem {
  provider_type: string;
  provider_name: string;
  is_enabled: boolean;
  is_default: boolean;
}

export interface SSOInitiateResponse {
  success: boolean;
  data: {
    redirect_url: string;
    state?: string;
  };
  message: string;
}

export interface SSOCallbackResponse {
  success: boolean;
  data: {
    user: any;
    token: string;
  };
  message: string;
}

export interface SSOTestResponse {
  success: boolean;
  message: string;
  data?: {
    user_info?: any;
    connection_status?: string;
  };
}

/**
 * Get list of available SSO providers (public endpoint)
 */
export const getSSOProviders = async (): Promise<SSOProviderListItem[]> => {
  const response = await axios.get(`${API_BASE_URL}/api/sso/providers`);
  return response.data.data;
};

/**
 * Get specific SSO provider details (admin only)
 */
export const getSSOProvider = async (providerId: number): Promise<SSOProvider> => {
  const token = localStorage.getItem('token');
  const response = await axios.get(`${API_BASE_URL}/api/sso/providers/${providerId}`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  return response.data.data;
};

/**
 * Initiate SSO authentication flow
 */
export const initiateSSOAuth = async (providerType: string): Promise<SSOInitiateResponse> => {
  const response = await axios.get(`${API_BASE_URL}/api/sso/${providerType}/initiate`);
  return response.data;
};

/**
 * Handle SSO callback after authentication
 */
export const handleSSOCallback = async (
  providerType: string,
  code?: string,
  state?: string,
  samlResponse?: string
): Promise<SSOCallbackResponse> => {
  const response = await axios.post(`${API_BASE_URL}/api/sso/callback`, {
    provider_type: providerType,
    code,
    state,
    saml_response: samlResponse,
  });
  return response.data;
};

/**
 * Configure SSO provider (admin only)
 */
export const configureSSOProvider = async (
  providerType: 'saml' | 'oauth' | 'oidc' | 'ldap',
  providerName: string,
  config: SSOProvider['config'],
  isEnabled: boolean = true,
  isDefault: boolean = false
): Promise<SSOProvider> => {
  const token = localStorage.getItem('token');
  const response = await axios.post(
    `${API_BASE_URL}/api/sso/configure`,
    {
      provider_type: providerType,
      provider_name: providerName,
      config,
      is_enabled: isEnabled,
      is_default: isDefault,
    },
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );
  return response.data.data;
};

/**
 * Test SSO provider connection (admin only)
 */
export const testSSOProvider = async (providerId: number): Promise<SSOTestResponse> => {
  const token = localStorage.getItem('token');
  const response = await axios.post(
    `${API_BASE_URL}/api/sso/providers/${providerId}/test`,
    {},
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    }
  );
  return response.data;
};

/**
 * Delete SSO provider (admin only)
 */
export const deleteSSOProvider = async (providerId: number): Promise<void> => {
  const token = localStorage.getItem('token');
  await axios.delete(`${API_BASE_URL}/api/sso/providers/${providerId}`, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
};

export default {
  getSSOProviders,
  getSSOProvider,
  initiateSSOAuth,
  handleSSOCallback,
  configureSSOProvider,
  testSSOProvider,
  deleteSSOProvider,
};
