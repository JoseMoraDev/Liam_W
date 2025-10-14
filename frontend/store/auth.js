// ~/store/auth.js
import { ref } from "vue";
import { axiosClient } from "~/axiosConfig";
import { useCookie } from "#app";

export const userLoggedIn = ref(false);
export const userData = ref(null);

// ✅ Login: guardar token y usuario
export function login(token, user) {
  const tokenCookie = useCookie("token");
  tokenCookie.value = token;
  userData.value = user;
  userLoggedIn.value = true;
}

// ✅ Logout
export function logout() {
  const tokenCookie = useCookie("token");
  tokenCookie.value = null;
  userData.value = null;
  userLoggedIn.value = false;
}

// ✅ Cargar sesión existente al recargar la app
export async function checkAuth() {
  const token = useCookie("token").value;
  
  if (!token) {
    logout();
    return false;
  }
  
  try {
    const response = await axiosClient.get("/me", {
      headers: { Authorization: `Bearer ${token}` },
    });
    
    // Laravel devuelve { user: {...} }
    if (response.data?.user) {
      console.log('okoo'); // ESTO NO SE EJECUTA
      userData.value = response.data.user;
      userLoggedIn.value = true;
      
      return true;
    }
  } catch (error) {
    console.error("❌ Error al comprobar sesión:", error);
    logout();
    return false;
  }
}
