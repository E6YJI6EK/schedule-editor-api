import axios from "axios";

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE,
  timeout: 15000,
  headers: { "Content-Type": "application/json" },
  withCredentials: true
});

const AUTH_SKIP_REDIRECT = ['/auth/login', '/auth/me']

http.interceptors.response.use(
  (res) => res,
  (err) => {
    const url: string = err?.config?.url ?? ''
    const is401 = err?.response?.status === 401
    const isAuthEndpoint = AUTH_SKIP_REDIRECT.some(u => url.includes(u))

    if (is401 && !isAuthEndpoint) {
      window.location.replace('/login')
      return new Promise(() => {})
    }

    return Promise.reject(
      err?.response?.data ?? {
        success: false,
        message: "Network error",
        status: 0,
      }
    )
  }
);
