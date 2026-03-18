import axios from 'axios';

// Khởi tạo một đối tượng (instance) axios, gắn cứng URL của Backend Laravel
const api = axios.create({
    baseURL: 'http://127.0.0.1:8001/api', // Địa chỉ Backend
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// Interceptor (Kẻ đánh chặn): Trước khi bất kỳ request nào được gửi lên server...
api.interceptors.request.use((config) => {
    // 1. Phải lên LocalStorage tìm vòng lấy token xem user có đang đăng nhập không
    const token = localStorage.getItem('token');
    
    // 2. Nếu có, đính kèm token như 1 thẻ VIP vào Header
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
