import axios from 'axios';

export const api = axios.create({
  baseURL: 'https://web-prog-project-git-prod-mideverys-projects.vercel.app/api',
  withCredentials: true,
});



