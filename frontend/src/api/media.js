import axios from 'axios';
const backendDomain = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8000';

export const api = axios.create({
  baseURL: `${backendDomain}/api`, 
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export const resolveImageUrl = (path, fallback) => {
  if (!path) return fallback;
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  const cleanPath = path.replace(/^\/+/, '');
  return `${backendDomain}/${cleanPath}`;
};