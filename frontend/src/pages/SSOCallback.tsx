import React, { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { handleSSOCallback } from '../services/ssoService';
import { setUser } from '../store/authSlice';

export const SSOCallback: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [error, setError] = useState<string | null>(null);
  const [processing, setProcessing] = useState(true);

  useEffect(() => {
    processCallback();
  }, []);

  const processCallback = async () => {
    try {
      setProcessing(true);

      // Get callback parameters
      const code = searchParams.get('code');
      const state = searchParams.get('state');
      const samlResponse = searchParams.get('SAMLResponse');

      // Get stored provider and state
      const storedProvider = sessionStorage.getItem('sso_provider');
      const storedState = sessionStorage.getItem('sso_state');

      // Validate state for OAuth/OIDC
      if (state && storedState && state !== storedState) {
        throw new Error('Invalid state parameter. Possible CSRF attack.');
      }

      if (!storedProvider) {
        throw new Error('No SSO provider found in session');
      }

      // Handle the callback
      const response = await handleSSOCallback(
        storedProvider,
        code || undefined,
        state || undefined,
        samlResponse || undefined
      );

      if (response.success) {
        // Store token and user info
        localStorage.setItem('token', response.data.token);
        dispatch(setUser(response.data.user));

        // Clear session storage
        sessionStorage.removeItem('sso_provider');
        sessionStorage.removeItem('sso_state');

        // Redirect to dashboard
        setTimeout(() => {
          navigate('/dashboard');
        }, 500);
      } else {
        throw new Error(response.message || 'SSO authentication failed');
      }
    } catch (error: any) {
      console.error('SSO callback error:', error);
      setError(error.response?.data?.message || error.message || 'SSO authentication failed');
      setProcessing(false);

      // Redirect to login after 3 seconds
      setTimeout(() => {
        navigate('/sso-login');
      }, 3000);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div className="text-center">
          {processing && !error && (
            <>
              <div className="flex justify-center mb-4">
                <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600"></div>
              </div>
              <h2 className="text-2xl font-bold text-gray-900 mb-2">
                Completing SSO Sign-In
              </h2>
              <p className="text-gray-600">Please wait while we authenticate you...</p>
            </>
          )}

          {error && (
            <div className="bg-white p-8 rounded-lg shadow-md">
              <div className="text-red-600 text-5xl mb-4">⚠️</div>
              <h2 className="text-2xl font-bold text-gray-900 mb-2">
                Authentication Failed
              </h2>
              <p className="text-gray-700 mb-4">{error}</p>
              <p className="text-sm text-gray-500">
                Redirecting to login page...
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SSOCallback;
