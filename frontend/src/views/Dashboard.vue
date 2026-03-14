<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-indigo-600 shadow">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-white">WorkFlowy</h1>
                
                <!-- Thanh công cụ User -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-indigo-100" v-if="authStore.user">
                        Xin chào, {{ authStore.user.name }}
                    </span>
                    <button @click="handleLogout" class="px-3 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-md text-sm font-medium transition-colors">
                        Đăng Xuất
                    </button>
                </div>
            </div>
        </header>

        <main>
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <!-- Vùng chứa Dashboard card -->
                <div class="px-4 py-6 sm:px-0">
                    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-indigo-500">
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Tổng quan Dự Án</h2>
                        <div class="border-4 border-dashed border-gray-200 rounded-lg h-64 flex justify-center items-center text-gray-500">
                            (Danh sách Dự án sẽ hiển thị ở đây ở Giai đoạn sau)
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Khi vào trang Dashboard, ưu tiên kéo lại thông tin cá nhân mới nhất (nếu chưa có)
onMounted(async () => {
    if (!authStore.user) {
        await authStore.fetchUser();
    }
});

const handleLogout = () => {
    authStore.logout();
    router.push({ name: 'login' });
};
</script>
