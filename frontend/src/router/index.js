import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Định nghĩa các "Đường dẫn" (Routes)
const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/Login.vue'), // Lazy loading component
        meta: { guestOnly: true } // Chỉ dành cho IP Khách (Chưa đăng nhập)
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: { requiresAuth: true } // Phải đăng nhập mới được vào
    },
    {
        path: '/projects/:id',
        name: 'kanban',
        component: () => import('../views/KanbanBoard.vue'),
        props: true, // Kích hoạt truyền :id vào component KanbanBoard.vue
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// "Kẻ Gác Cổng" (Navigation Guard)
router.beforeEach((to, from, next) => {
    // Kéo thông tin xem user có Token đăng nhập hay chưa
    const authStore = useAuthStore();
    
    // Nếu trang đó yêu cầu đăng nhập (requiresAuth) mà lại chưa có Token (isAuthenticated = false)
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' }); // Đá thẳng về trang login
    } 
    // Nếu trang đó chỉ cho khách chưa login (ví dụ trang Đăng Ký, Đăng Nhập), mà user LẠI đang login rồi...
    else if (to.meta.guestOnly && authStore.isAuthenticated) {
        next({ name: 'dashboard' }); // Bắt vào luôn dashboard
    } 
    // Các trường hợp khác cho đi qua bình thường
    else {
        next();
    }
});

export default router;
