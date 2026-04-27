import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
});

export const getItems = async () => {
  const response = await api.get('/items');
  return response.data;
};

export const getCustomers = async () => {
  const response = await api.get('/customers');
  return response.data;
};

export const createCustomer = async (data: any) => {
  const response = await api.post('/customers', data);
  return response.data;
};

export const createSalesOrder = async (data: any) => {
  const response = await api.post('/salesorders', data);
  return response.data;
};

export default api;
