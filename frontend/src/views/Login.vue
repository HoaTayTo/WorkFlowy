<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Đăng nhập hệ thống
            </h2>
             <p class="mt-2 text-center text-sm text-gray-600">
                Chưa có tài khoản?
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Đăng ký ngay
                </a>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- Vùng hiển thị lỗi đăng nhập -->
                <div v-if="errorMsg" class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ errorMsg }}</span>
                </div>

                <!-- Lắng nghe sự kiện submit, ngăn chặn việc load lại trang bằng .prevent -->
                <form class="space-y-6" @submit.prevent="handleLogin">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Địa chỉ Email</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                v-model="form.email"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                v-model="form.password"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Nhớ mật khẩu
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            :disabled="isLoading"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span v-if="isLoading">Đang xử lý...</span>
                            <span v-else>Đăng Nhập</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Biến lưu trạng thái thông báo và loading
const errorMsg = ref('');
const isLoading = ref(false);

// Reactive object lưu trữ dữ liệu v-model từ form
const form = reactive({
    email: 'test_cv@workflowy.com', // Điền sẵn cho tiện test
    password: 'password123'
});

const handleLogin = async () => {
    isLoading.value = true;
    errorMsg.value = ''; // Reset lỗi cũ
    
    try {
        // Gửi lệnh sang Kho state management (Pinia API logic)
        await authStore.login(form);
        
        // Nếu qua ải thành công, chuyển Router (Điều hướng) tới màn hình chính (Dashboard)
        router.push({ name: 'dashboard' });
    } catch (error) {
        // Nếu Backend trả về mã lỗi 422 (Không đúng mật khẩu/email)
        if (error.response && error.response.status === 422) {
            errorMsg.value = error.response.data.message || 'Thông tin đăng nhập không chính xác.';
        } else {
            errorMsg.value = 'Mất kết nối với máy chủ. Xin vui lòng thử lại.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>
