<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-indigo-600 shadow">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-white">WorkFlowy</h1>
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
                <!-- Chỗ tạo dự án mới -->
                <div class="mb-6 bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Tạo Dự Án Mới</h2>
                    <form @submit.prevent="handleCreateProject" class="flex gap-4">
                        <input v-model="newProjectName" type="text" required placeholder="Nhập tên dự án (VD: Phát triển Website Bán Hàng)" class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition">Tạo Mới</button>
                    </form>
                </div>

                <!-- Lưới hiển thị dự án -->
                <div v-if="projectStore.isLoading" class="text-center py-10">Đang tải danh sách...</div>
                
                <div v-else-if="projectStore.projects.length === 0" class="bg-white rounded-lg shadow p-6 border-t-4 border-indigo-500 flex justify-center items-center h-48 text-gray-500">
                    Bạn chưa có dự án nào. Hãy gõ tên và tạo một cái mới ngay phía trên nhé!
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Thẻ dự án (Card) có RouterLink trỏ tới đường dẫn /projects/:id -->
                    <RouterLink v-for="project in projectStore.projects" :key="project.id" :to="`/projects/${project.id}`"
                        class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300 p-6 border group hover:border-indigo-400 cursor-pointer block">
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors mb-2">{{ project.name }}</h3>
                        <p class="text-sm text-gray-500" v-if="project.description">{{ project.description }}</p>
                        <p class="text-sm text-gray-500" v-else>Không có mô tả</p>
                        <div class="mt-4 text-xs text-gray-400">Tạo ngày: {{ new Date(project.created_at).toLocaleDateString('vi-VN') }}</div>
                    </RouterLink>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useProjectStore } from '../stores/project';

const router = useRouter();
const authStore = useAuthStore();
const projectStore = useProjectStore();

const newProjectName = ref('');

// Kéo thông tin
onMounted(async () => {
    if (!authStore.user) {
        await authStore.fetchUser();
    }
    await projectStore.fetchProjects(); // Lấy tất cả project từ API
});

const handleCreateProject = async () => {
    if (newProjectName.value.trim() === '') return;
    
    await projectStore.addProject({
        name: newProjectName.value,
        description: '' // Giai đoạn này để trống, có thể mở rộng bảng sau
    });
    newProjectName.value = ''; // Reset input
};

const handleLogout = () => {
    authStore.logout();
    router.push({ name: 'login' });
};
</script>
