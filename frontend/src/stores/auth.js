import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
    // STATE: "Kho chứa" dữ liệu chung, ai cũng đọc được
    state: () => ({
        user: null, // Thông tin người dùng
        token: localStorage.getItem('token') || null, // Chuỗi khóa
        isAuthenticated: !!localStorage.getItem('token'), 
    }),

    // ACTIONS: Các hành động để chỉnh sửa nhà kho (Ví dụ: Thực hiện gọi API đăng nhập)
    actions: {
        async login(credentials) {
            // 1. Gọi backend để lấy Token
            const response = await api.post('/login', credentials);
            
            // 2. Cất Token vào State và LocalStorage (để lần sau vào app không bắt đăng nhập lại)
            this.token = response.data.access_token;
            this.isAuthenticated = true;
            this.user = response.data.user;
            localStorage.setItem('token', this.token);
            
            return response;
        },

        async fetchUser() {
            try {
                // Tự động sử dụng Token nãy giờ sinh ra để vào lấy Info
                const response = await api.get('/user');
                this.user = response.data;
            } catch (error) {
                this.logout();
            }
        },

        logout() {
            // Dọn sạch mọi thông tin đăng nhập
            api.post('/logout').catch(() => {});
            this.user = null;
            this.token = null;
            this.isAuthenticated = false;
            localStorage.removeItem('token');
        }
    }
});
