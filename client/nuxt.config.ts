export default defineNuxtConfig({
  devtools: { enabled: true },
  ssr: false,
  plugins: [
  ],
  modules: [
    '@nuxtjs/tailwindcss',
    'nuxt-sanctum-auth',
  ],
  runtimeConfig: {
    public: {
      apiEndpoint: process.env.API_ENDPOINT,
      apiPath: process.env.API_PATH,
      pusherAppKey: process.env.PUSHER_APP_KEY,
      pusherHost: process.env.PUSHER_HOST,
      pusherPort: process.env.PUSHER_PORT,
      pusherScheme: process.env.PUSHER_SCHEME,
      pusherAppCluster: process.env.PUSHER_APP_CLUSTER,
    }
  },
  nuxtSanctumAuth: {
    token: true,
    baseUrl: process.env.API_ENDPOINT,
    endpoints: {
      csrf: '/sanctum/csrf-cookie',
      login: process.env.API_PATH+'/login',
      logout: process.env.API_PATH+'/logout',
      user: process.env.API_PATH+'/user'
    },
    csrf: {
      headerKey: 'X-XSRF-TOKEN',
      cookieKey: 'XSRF-TOKEN',
      tokenCookieKey: 'nuxt-sanctum-auth-token'
    },
    redirects: {
      home: '/',
      login: '/login',
      logout: '/logout'
    }
  }
})
