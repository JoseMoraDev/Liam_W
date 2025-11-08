import { axiosClient } from "~/axiosConfig";
import { useCookie } from "#app";

// Estado compartido entre SSR y cliente
export const userLoggedIn = () => useState("userLoggedIn", () => false);
export const userData = () => useState("userData", () => null);

// Login: guarda token y usuario
export async function login(user, token) {
  const tokenCookie = useCookie("token", { path: "/" });
  tokenCookie.value = token;

  userData().value = user;
  userLoggedIn().value = true;

  // Hidratar con /me para incluir campos como role/is_blocked
  try {
    const me = await axiosClient.get("/me");
    if (me?.data?.user) {
      userData().value = me.data.user;
    }
  } catch {}
}

// Logout: limpia token y datos de usuario
export function logout() {
  const tokenCookie = useCookie("token", { path: "/" });
  tokenCookie.value = null;

  userData().value = null;
  userLoggedIn().value = false;
}

// Comprueba sesión activa
export async function checkAuth() {
  const token = useCookie("token", { path: "/" }).value;
  // console.log("[checkAuth] token:", token);

  if (!token) {
    logout();
    return false;
  }

  try {
    const response = await axiosClient.get("/me", {
      headers: { Authorization: `Bearer ${token}` },
    });
    // console.log("[checkAuth] /me response:", response.data);

    if (response.data?.user) {
      userData().value = response.data.user;
      userLoggedIn().value = true;
      return true;
    }

    logout();
    return false;
  } catch (err) {
    console.error("[checkAuth] error:", err);
    logout();
    return false;
  }
}

