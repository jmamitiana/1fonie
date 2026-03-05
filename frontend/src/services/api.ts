import axios from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
});

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      if (typeof window !== 'undefined') {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  register: (data: any) => api.post('/auth/register', data),
  login: (data: any) => api.post('/auth/login', data),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
  updateProfile: (data: any) => api.put('/auth/profile', data),
  updatePassword: (data: any) => api.put('/auth/password', data),
  forgotPassword: (data: any) => api.post('/auth/forgot-password', data),
  resetPassword: (data: any) => api.post('/auth/reset-password', data),
};

// Mission API
export const missionAPI = {
  getAll: (params?: any) => api.get('/missions', { params }),
  getById: (id: number) => api.get(`/missions/${id}`),
  getCategories: () => api.get('/categories'),
  getCities: () => api.get('/cities'),
};

// Company API
export const companyAPI = {
  getProfile: () => api.get('/company/profile'),
  updateProfile: (data: any) => api.put('/company/profile', data),
  getMissions: (params?: any) => api.get('/company/missions', { params }),
  createMission: (data: any) => api.post('/company/missions', data),
  getMission: (id: number) => api.get(`/company/missions/${id}`),
  updateMission: (id: number, data: any) => api.put(`/company/missions/${id}`, data),
  deleteMission: (id: number) => api.delete(`/company/missions/${id}`),
  getMissionApplications: (id: number) => api.get(`/company/missions/${id}/applications`),
  selectProvider: (missionId: number, applicationId: number) =>
    api.post(`/company/missions/${missionId}/select-provider`, { application_id: applicationId }),
  payMission: (id: number) => api.post(`/company/missions/${id}/pay`),
  completeMission: (id: number) => api.post(`/company/missions/${id}/complete`),
  getPayments: (params?: any) => api.get('/company/payments', { params }),
  getDashboard: () => api.get('/company/dashboard'),
};

// Provider API
export const providerAPI = {
  getProfile: () => api.get('/provider/profile'),
  updateProfile: (data: any) => api.put('/provider/profile', data),
  connectStripe: () => api.post('/provider/stripe-connect'),
  toggleAvailability: () => api.put('/provider/availability'),
  getMissions: (params?: any) => api.get('/provider/missions', { params }),
  getAvailableMissions: (params?: any) => api.get('/provider/missions/available', { params }),
  getMission: (id: number) => api.get(`/provider/missions/${id}`),
  applyMission: (id: number, data: any) => api.post(`/provider/missions/${id}/apply`, data),
  withdrawApplication: (id: number) => api.post(`/provider/missions/${id}/withdraw`),
  getApplications: (params?: any) => api.get('/provider/applications', { params }),
  getEarnings: () => api.get('/provider/earnings'),
  getDashboard: () => api.get('/provider/dashboard'),
};

// Message API
export const messageAPI = {
  getByMission: (missionId: number) => api.get(`/messages/${missionId}`),
  send: (missionId: number, data: any) => api.post(`/messages/${missionId}`, data),
  markAsRead: (messageId: number) => api.put(`/messages/${messageId}/read`),
};

// Review API
export const reviewAPI = {
  create: (missionId: number, data: any) => api.post(`/missions/${missionId}/reviews`, data),
  getProviderReviews: (providerId: number) => api.get(`/providers/${providerId}/reviews`),
  getCompanyReviews: (companyId: number) => api.get(`/companies/${companyId}/reviews`),
};

// Notification API
export const notificationAPI = {
  getAll: (params?: any) => api.get('/notifications', { params }),
  markAsRead: (id: number) => api.put(`/notifications/${id}/read`),
  markAllAsRead: () => api.put('/notifications/read-all'),
};

// Payment API
export const paymentAPI = {
  createIntent: (missionId: number) => api.post(`/payments/${missionId}/create-intent`),
  getById: (id: number) => api.get(`/payments/${id}`),
  webhook: () => api.post('/payments/webhook'),
};

// Admin API
export const adminAPI = {
  getDashboard: () => api.get('/admin/dashboard'),
  getUsers: (params?: any) => api.get('/admin/users', { params }),
  toggleUserActive: (userId: number) => api.put(`/admin/users/${userId}/toggle-active`),
  deleteUser: (userId: number) => api.delete(`/admin/users/${userId}`),
  getMissions: (params?: any) => api.get('/admin/missions', { params }),
  updateMissionStatus: (missionId: number, status: string) =>
    api.put(`/admin/missions/${missionId}/status`, { status }),
  getPayments: (params?: any) => api.get('/admin/payments', { params }),
  getAnalytics: () => api.get('/admin/analytics'),
  getDisputes: (params?: any) => api.get('/admin/disputes', { params }),
  resolveDispute: (missionId: number, data: any) =>
    api.post(`/admin/disputes/${missionId}/resolve`, data),
};

export default api;
