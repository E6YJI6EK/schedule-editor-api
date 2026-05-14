import { createRouter, createWebHistory, type RouteRecordRaw } from "vue-router";
import { useAuthStore } from "@/screens/auth/model/authStore";
import ScheduleScreen from "@/screens/schedule/view.vue";
import LoginScreen from "@/screens/auth/view.vue";
import AdminScreen from "@/screens/admin/view.vue";
import PortalScreen from "@/screens/portal/view.vue";

const routes: RouteRecordRaw[] = [
  { path: "/", redirect: "/portal" },
  { path: "/portal", name: "Portal", component: PortalScreen },
  { path: "/login", name: "Login", component: LoginScreen },
  {
    path: "/schedule",
    name: "Schedule",
    component: ScheduleScreen,
    meta: { requiresAuth: true },
  },
  {
    path: "/admin",
    name: "Admin",
    component: AdminScreen,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: "Login" };
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return { name: "Schedule" };
  }

  if (to.name === "Login" && authStore.isAuthenticated) {
    return { name: "Schedule" };
  }
});

export default router;
