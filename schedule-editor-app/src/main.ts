import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from '@/app/router'
import './style.css'
import App from '@/app/App.vue'
import { useAuthStore } from '@/screens/auth/model/authStore'

async function bootstrap() {
  const app = createApp(App)
  const pinia = createPinia()
  app.use(pinia)

  await useAuthStore().initialize()

  app.use(router)
  app.mount('#app')
}

bootstrap()
