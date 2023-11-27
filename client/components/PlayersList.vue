<script setup>
import { ref } from "vue";


var props = defineProps(['admins', 'users', 'userId', 'blocked', 'gamesPerMatch']);

var opened = ref(false)

</script>

<template>
    <div class=" bg-white border-r overflow-x-hidden border-gray-300 fixed z-[997] top-[61px] md:top-[1px] left-0 h-full md:relative transition-all duration-300" 
    :class="!opened ? 'border-r w-closed' : 'border-none w-opened'"
    >

      <div class="fixed md:hidden top-0 left-0 w-[65px] h-[60px] flex items-center justify-center bg-primary-500" @click="opened=true" v-if="!opened">
        <svg xmlns="http://www.w3.org/2000/svg" height="2rem" class="fill-white " viewBox="0 0 512 512"><path d="M470.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 256 265.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160zm-352 160l160-160c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L210.7 256 73.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0z"/></svg>
      </div>
      <div class="fixed md:hidden top-0 left-0 w-[65px] h-[60px] flex items-center justify-center bg-primary-500" @click="opened=false" v-else>
        <svg xmlns="http://www.w3.org/2000/svg" height="2em" class="fill-white " viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
      </div>

      <div class="overflow-y-auto h-[calc(100%-61px)] py-4 md:px-4 w-[300px]">

        <div class=" font-black text-sm mb-2 p-2 text-gray-400 hidden md:block" v-if="admins.length > 0">
          Amministratori
        </div>
        <div class="flex items-center mb-4 p-2 rounded-md" v-for="user in admins" :key="user.id" :class="blocked.includes(user.id) ? 'opacity-40' : ''">
          <div class="w-12 h-12 bg-gray-300 rounded-full mr-3">
            <img :src="'https://i.pravatar.cc/300?u='+user.id" alt="User Avatar" class="w-12 h-12 rounded-full border-primary-500" :class="userId == user.id ? 'border-4' : 'border-0'">
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold" :class="userId == user.id ? 'text-primary-500' : ''">{{ user.name }}</h2>
            <!-- <p class="text-gray-400 text-xs">{{ user.admin ? 'Admin' : 'Player' }}</p> -->
          </div>
        </div>

        <div class="h-8"></div>

        <div class="font-black text-sm mb-2 p-2 text-gray-400 items-center hidden md:flex" v-if="users.length > 0">
          <div class="flex-1">
            Giocatori
          </div>
          <div class="p-0 w-8 flex justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" class="fill-yellow-500" viewBox="0 0 576 512"><path d="M400 0H176c-26.5 0-48.1 21.8-47.1 48.2c.2 5.3 .4 10.6 .7 15.8H24C10.7 64 0 74.7 0 88c0 92.6 33.5 157 78.5 200.7c44.3 43.1 98.3 64.8 138.1 75.8c23.4 6.5 39.4 26 39.4 45.6c0 20.9-17 37.9-37.9 37.9H192c-17.7 0-32 14.3-32 32s14.3 32 32 32H384c17.7 0 32-14.3 32-32s-14.3-32-32-32H357.9C337 448 320 431 320 410.1c0-19.6 15.9-39.2 39.4-45.6c39.9-11 93.9-32.7 138.2-75.8C542.5 245 576 180.6 576 88c0-13.3-10.7-24-24-24H446.4c.3-5.2 .5-10.4 .7-15.8C448.1 21.8 426.5 0 400 0zM48.9 112h84.4c9.1 90.1 29.2 150.3 51.9 190.6c-24.9-11-50.8-26.5-73.2-48.3c-32-31.1-58-76-63-142.3zM464.1 254.3c-22.4 21.8-48.3 37.3-73.2 48.3c22.7-40.3 42.8-100.5 51.9-190.6h84.4c-5.1 66.3-31.1 111.2-63 142.3z"/></svg>
          </div>
        </div>
        <div class="flex items-center mb-4 p-2" v-for="user in users" :key="user.id" :class="blocked.includes(user.id) ? 'opacity-40' : ''">
          <div class="flex items-center flex-1">
            <div class="w-12 h-12 bg-gray-300 rounded-full mr-3 relative">
              <div class="absolute inline-flex md:hidden items-center justify-center w-7 h-7 text-xs font-bold text-white bg-yellow-500 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900">
                  {{ user.matches }}
              </div>
              <div class="absolute inline-flex items-center justify-center w-7 h-7 text-xs font-bold text-white bg-green-500 border-2 border-white rounded-full -bottom-2 -end-2 dark:border-gray-900">
                  {{ user.games }}/{{ gamesPerMatch }}
              </div>
              <img :src="'https://i.pravatar.cc/300?u='+user.id" alt="User Avatar" class="w-12 h-12 rounded-full border-primary-500" :class="userId == user.id ? 'border-4' : 'border-0'">
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold" :class="userId == user.id ? 'text-primary-500' : ''">{{ user.name }}</h2>
            </div>
          </div>
          <div class="bg-primary-100 p-2 w-8 flex items-center justify-center font-black text-primary-500">
            {{ user.matches }}
          </div>
        </div>
        
      </div>
      <div>
        
      </div>
    </div> 
</template>

<style scoped>

.w-closed {
  @apply w-[65px] md:w-[300px];
}
.w-opened {
  @apply w-full md:w-[300px];
}

</style>