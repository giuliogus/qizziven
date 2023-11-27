<template>
  <nav class="bg-white dark:bg-gray-900 fixed w-full z-20 top-0 start-0 border-b border-gray-200 dark:border-gray-600">
    <div
      class="flex flex-wrap items-center justify-between mx-auto h-[60px] px-4"
    >
      <NuxtLink to="/" class="flex gap-4 ml-[60px] md:ml-0 pl-4 md:pl-0 transition-all duration-300">
        <Logo />
        <span
          class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"
        >
          Quizz<span class="text-primary-500">iven</span>
        </span>
      </NuxtLink>
      <div class="block md:w-auto" id="navbar-default">
        <ul
          class="font-medium flex  p-0 rounded-lg flex-row space-x-8 rtl:space-x-reverse mt-0 border-0  dark:bg-gray-800"
        >
          <li>
            <NuxtLink 
            to="/logout"
            class="block py-2 px-3 text-gray-900 rounded hover:bg-transparent border-0 hover:text-primary-700 p-0 dark:text-white dark:hover:text-primary-500 dark:hover:bg-transparent"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="fill-primary-500" height="1.5em" viewBox="0 0 512 512"><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0z"/></svg>
            </NuxtLink>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
