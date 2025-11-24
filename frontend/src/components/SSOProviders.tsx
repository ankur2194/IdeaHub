import React, { useEffect, useState } from 'react';
import { getSSOProviders, initiateSSOAuth, SSOProviderListItem } from '../services/ssoService';

interface SSOProvidersProps {
  onError?: (error: string) => void;
}

const providerIcons: Record<string, string> = {
  saml: '🔐',
  oauth: '🔑',
  oidc: '🎫',
  ldap: '📁',
};

const providerColors: Record<string, string> = {
  saml: 'bg-blue-100 text-blue-700 border-blue-300',
  oauth: 'bg-green-100 text-green-700 border-green-300',
  oidc: 'bg-purple-100 text-purple-700 border-purple-300',
  ldap: 'bg-orange-100 text-orange-700 border-orange-300',
};

export const SSOProviders: React.FC<SSOProvidersProps> = ({ onError }) => {
  const [providers, setProviders] = useState<SSOProviderListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [initiating, setInitiating] = useState<string | null>(null);

  useEffect(() => {
    loadProviders();
  }, []);

  const loadProviders = async () => {
    try {
      setLoading(true);
      const data = await getSSOProviders();
      setProviders(data.filter((p) => p.is_enabled));
    } catch (error: any) {
      const errorMsg = error.response?.data?.message || 'Failed to load SSO providers';
      onError?.(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const handleProviderClick = async (providerType: string) => {
    try {
      setInitiating(providerType);
      const response = await initiateSSOAuth(providerType);

      if (response.success && response.data.redirect_url) {
        // Store state for OAuth/OIDC
        if (response.data.state) {
          sessionStorage.setItem('sso_state', response.data.state);
          sessionStorage.setItem('sso_provider', providerType);
        }

        // Redirect to SSO provider
        window.location.href = response.data.redirect_url;
      }
    } catch (error: any) {
      const errorMsg = error.response?.data?.message || 'Failed to initiate SSO authentication';
      onError?.(errorMsg);
      setInitiating(null);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center py-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (providers.length === 0) {
    return (
      <div className="text-center py-8 text-gray-500">
        <p>No SSO providers available</p>
        <p className="text-sm mt-2">Please use regular login or contact your administrator</p>
      </div>
    );
  }

  return (
    <div className="space-y-3">
      <p className="text-sm text-gray-600 text-center mb-4">
        Sign in with your organization's SSO
      </p>
      {providers.map((provider) => (
        <button
          key={provider.provider_type}
          onClick={() => handleProviderClick(provider.provider_type)}
          disabled={initiating !== null}
          className={`
            w-full flex items-center justify-center gap-3 px-4 py-3 rounded-lg border-2
            transition-all duration-200 font-medium
            ${providerColors[provider.provider_type] || 'bg-gray-100 text-gray-700 border-gray-300'}
            ${initiating === provider.provider_type ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md hover:scale-105'}
            ${provider.is_default ? 'ring-2 ring-offset-2 ring-blue-500' : ''}
          `}
        >
          <span className="text-2xl">{providerIcons[provider.provider_type] || '🔒'}</span>
          <span>
            {initiating === provider.provider_type ? (
              <>
                <span className="inline-block animate-spin mr-2">⏳</span>
                Redirecting...
              </>
            ) : (
              <>
                Continue with {provider.provider_name}
                {provider.is_default && (
                  <span className="ml-2 text-xs bg-blue-500 text-white px-2 py-0.5 rounded">
                    Default
                  </span>
                )}
              </>
            )}
          </span>
        </button>
      ))}
    </div>
  );
};

export default SSOProviders;
