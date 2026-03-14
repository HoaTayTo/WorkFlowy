<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();

// Chạy một lần khi App tải: lấy thông tin người dùng từ localStorage 
onMounted(() => {
  if (authStore.isAuthenticated) {
    authStore.fetchUser();
  }
});
</script>

<template>
  <!-- <router-view> là ô trống. Nếu vào trang /login, component Login.vue sẽ nhét vào đây. Nếu vào /, Dashboard sẽ được nhét vào. -->
  <router-view></router-view>
</template>
