<script setup>
definePageMeta({
  middleware: 'auth'
});
</script>

<template>
    <div>
      <NavBar />
      <Quiz />
    </div>
</template>
