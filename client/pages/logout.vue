<script setup>

definePageMeta({
  middleware: "auth",
});

const { $sanctumAuth } = useNuxtApp();
const router = useRouter();
const errors = ref([]);

setTimeout(() => {
  $sanctumAuth.logout(
    (data) => {
      window.Echo.leave('quiz');
      router.push("/");
    }
  );
}, 1000)
</script>

<template>
  <section class="bg-gray-50 dark:bg-gray-900 h-screen">
    <div
      class="flex flex-col items-center justify-center px-6 py-8 mx-auto h-screen animate-pulse"
    >
      <Spinner />
      loggin out...
    </div>
  </section>
</template>
