import axios from 'axios';

const backendDomain = 'https://webprogproject.onrender.com';

export const api = axios.create({
  baseURL: `${backendDomain}/api`,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const resolveImageUrl = (path, fallback) => {
  if (!path) return fallback;
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  const cleanPath = path.replace(/^\/+/, '');
  return `${backendDomain}/${cleanPath}`;
};