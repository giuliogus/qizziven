<script setup>
definePageMeta({
  middleware: 'guest'
})

const { $sanctumAuth } = useNuxtApp();
const router = useRouter();
const errors = ref([]);

var credentials = ref({
  email: null,
  password: null,
});

async function login() {
  try {
    await $sanctumAuth.login(
      credentials.value,
      // optional callback function
      (data) => {
        router.push("/");
      }
    );
  } catch (e) {
    // your error handling
    errors.value = e.errors;
  }
}
</script>

<template>
  <section class="bg-gray-50 dark:bg-gray-900 h-screen">
    <div
      class="flex flex-col items-center justify-center px-6 py-8 mx-auto h-full md:h-screen lg:py-0"
    >
      <NuxtLink to="/" class="flex gap-4 py-2">
          <Logo />
          <span
            class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"
          >
            Quizz<span class="text-primary-500">iven</span>
          </span>
      </NuxtLink>
      <div
        class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
          
          <form class="space-y-4 md:space-y-6" @submit.prevent="login">
            <div>
              <label
                for="email"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
              >
                Email
              </label>
              <input
                type="email"
                name="email"
                id="email"
                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="name@company.com"
                required=""
                v-model.trim="credentials.email"
              />
            </div>
            <div>
              <label
                for="password"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
              >
                Password
              </label>
              <input
                type="password"
                name="password"
                id="password"
                placeholder="••••••••"
                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                required=""
                v-model.trim="credentials.password"
              />
            </div>
            <button
              type="submit"
              class="w-full text-white bg-primary-500 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-500 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
            >
              Entra
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>
