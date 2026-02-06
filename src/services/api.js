import axios from 'axios';

export const BASE_URL = 'http://localhost:8080/college_fresh/backend';

// Create axios instance
const api = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor
api.interceptors.request.use(
  (config) => {
    const user = JSON.parse(localStorage.getItem('user'));
    // If we used a token, we would attach it here.
    // However, our primitive backend auth mostly relies on just passing IDs or checking session on server if we used session_start().
    // But since we are stateless REST, usually we send token.
    // Our login returned a JWT. Let's send it.
    if (user && user.jwt) {
      config.headers.Authorization = `Bearer ${user.jwt}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;