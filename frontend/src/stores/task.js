import { defineStore } from 'pinia';
import api from '../api';

export const useTaskStore = defineStore('task', {
    state: () => ({
        tasks: [],
        isLoading: false,
    }),
    getters: {
        // Phân loại task theo trạng thái
        todoTasks: (state) => state.tasks.filter(t => t.status === 'todo'),
        inProgressTasks: (state) => state.tasks.filter(t => t.status === 'in_progress'),
        reviewTasks: (state) => state.tasks.filter(t => t.status === 'review'),
        doneTasks: (state) => state.tasks.filter(t => t.status === 'done')
    },
    actions: {
        async fetchTasks(projectId) {
            this.isLoading = true;
            try {
                const response = await api.get(`/tasks?project_id=${projectId}`);
                this.tasks = response.data;
            } catch (error) {
                console.error("Lỗi tải danh sách công việc:", error);
            } finally {
                this.isLoading = false;
            }
        },
        async addTask(taskData) {
            try {
                const response = await api.post('/tasks', taskData);
                this.tasks.push(response.data);
                return response.data;
            } catch (error) {
                console.error("Lỗi tạo task mới:", error);
                throw error;
            }
        },
        async updateTaskStatus(taskId, newStatus) {
            // Cập nhật ngay trên UI cho mượt (Optimistic UI Update)
            const taskIndex = this.tasks.findIndex(t => t.id === taskId);
            const oldStatus = this.tasks[taskIndex].status;
            if (taskIndex !== -1) {
                this.tasks[taskIndex].status = newStatus;
            }
            
            try {
                // Gọi ngầm xuống Backend để đồng bộ
                await api.put(`/tasks/${taskId}`, { status: newStatus });
            } catch (error) {
                // Nếu báo lỗi, trả về trạng thái cũ
                this.tasks[taskIndex].status = oldStatus;
                console.error("Lỗi cập nhật trạng thái:", error);
            }
        },
        async deleteTask(taskId) {
            try {
                await api.delete(`/tasks/${taskId}`);
                this.tasks = this.tasks.filter(t => t.id !== taskId);
            } catch (error) {
                console.error("Lỗi xóa task:", error);
            }
        }
    }
});
