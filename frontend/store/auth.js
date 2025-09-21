import { ref } from 'vue';
import { axiosClient } from '~/axiosConfig';

export const userLoggedIn = ref(false);
export const userData = ref(null);

export function login(token) {
  if (import.meta.client) {
    localStorage.setItem('token', token);
  }
  userLoggedIn.value = true;
}

export function logout() {
  if (import.meta.client) {
    localStorage.removeItem('token');
  }
  userLoggedIn.value = false;
  userData.value = null;
}

export async function checkAuth() {
  if (!import.meta.client) return false;

  const token = localStorage.getItem('token');
  if (!token) {
    logout();
    return false;
  }

  try {
    const response = await axiosClient.get('/me', {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (response.status === 200) {
      userLoggedIn.value = true;
      userData.value = response.data;
      return true;
    }
  } catch (error) {
    logout();
    return false;
  }
}
