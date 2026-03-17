<template>
    <div class="relative" ref="dropdownRef">
        <!-- Nút Bấm Thay Đổi Màu Cho Phù Hợp Nền -->
        <button @click="toggleDropdown" 
            :class="[iconClass || 'text-gray-500 hover:text-indigo-600', 'relative p-2 focus:outline-none transition-colors']">
            
            <!-- Bell Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Unread Badge -->
            <span v-if="unreadCount > 0" class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
                {{ unreadCount }}
            </span>
        </button>

        <!-- Dropdown menu -->
        <div v-if="isOpen" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-20 border border-gray-100">
            <div class="py-2 px-4 bg-gray-50 border-b flex justify-between items-center">
                <span class="font-bold text-sm text-gray-700">Thông báo</span>
                <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Đánh dấu đã đọc tất cả</button>
            </div>
            <div class="max-h-80 overflow-y-auto">
                <div v-if="isLoading" class="p-4 text-center text-sm text-gray-500">Đang tải...</div>
                <div v-else-if="notifications.length === 0" class="p-4 text-center text-sm text-gray-500">Chưa có thông báo nào.</div>
                <div v-else>
                    <div v-for="notification in notifications" :key="notification.id" 
                         @click="handleNotificationClick(notification)"
                         :class="['p-4 border-b hover:bg-gray-50 cursor-pointer transition-colors', !notification.read_at ? 'bg-indigo-50/50' : '']">
                        <div class="flex justify-between items-start mb-1">
                            <h4 :class="['text-sm', !notification.read_at ? 'font-bold text-gray-900' : 'font-medium text-gray-700']">
                                {{ notification.data.title }}
                            </h4>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap ml-2">
                                {{ new Date(notification.created_at).toLocaleDateString('vi-VN') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-2">{{ notification.data.message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const props = defineProps({
    iconClass: { type: String, default: '' }
});

const router = useRouter();
const dropdownRef = ref(null);
const isOpen = ref(false);
const notifications = ref([]);
const isLoading = ref(false);

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const fetchNotifications = async () => {
    isLoading.value = true;
    try {
        const response = await api.get('/notifications');
        notifications.value = response.data;
    } catch (e) {
        console.error("Lỗi lấy thông báo", e);
    } finally {
        isLoading.value = false;
    }
};

const toggleDropdown = async () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await fetchNotifications();
    }
};

const markAsRead = async (id) => {
    try {
        await api.post(`/notifications/${id}/read`);
        const idx = notifications.value.findIndex(n => n.id === id);
        if (idx !== -1) {
            notifications.value[idx].read_at = new Date().toISOString();
        }
    } catch (e) {
        console.error(e);
    }
};

const markAllAsRead = async () => {
    try {
        await api.post('/notifications/mark-all-read');
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
    } catch (e) {
        console.error(e);
    }
};

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        await markAsRead(notification.id);
    }
    isOpen.value = false;
    // Chuyển hướng tới dự án chứa task
    if (notification.data.url) {
        router.push(notification.data.url);
    }
};

// Đóng dropdown khi click ra ngoài
const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>
