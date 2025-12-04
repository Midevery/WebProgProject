import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../api/client.js';

function HomePage() {
  const navigate = useNavigate();

  useEffect(() => {
    async function checkAuth() {
      try {
        await api.get('/me');
        navigate('/dashboard');
      } catch {
        navigate('/signin');
      }
    }
    checkAuth();
  }, [navigate]);

  return null;
}

export default HomePage;



