import axios from 'axios';

export const api = axios.create({
  baseURL: 'https://webprogproject.onrender.com/api',
  withCredentials: true,
});



