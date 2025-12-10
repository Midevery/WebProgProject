const backendBaseUrl =
  import.meta.env.VITE_BACKEND_URL?.replace(/\/$/, '') || 'http://localhost:8000';

export const resolveImageUrl = (path, fallback) => {
  if (!path) return fallback;
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  return `${backendBaseUrl}/${path.replace(/^\/+/, '')}`;
};


