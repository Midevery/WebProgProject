const backendBaseUrl =
  import.meta.env.VITE_BACKEND_URL?.replace(/\/$/, '') || 'https://webprogproject.onrender.com';

export function resolveImageUrl(path, fallback) {
  if (!path) return fallback;
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  return `${backendBaseUrl}/${path.replace(/^\/+/, '')}`;
}


