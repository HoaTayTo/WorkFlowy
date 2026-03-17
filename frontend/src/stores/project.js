import { defineStore } from 'pinia';
import api from '../api';

export const useProjectStore = defineStore('project', {
    state: () => ({
        projects: [],
        currentProject: null,
        isLoading: false,
    }),
    actions: {
        async fetchProjects() {
            this.isLoading = true;
            try {
                const response = await api.get('/projects');
                this.projects = response.data;
            } catch (error) {
                console.error("Lỗi khi tải danh sách dự án:", error);
            } finally {
                this.isLoading = false;
            }
        },
        async addProject(projectData) {
            try {
                const response = await api.post('/projects', projectData);
                this.projects.push(response.data);
                return response.data;
            } catch (error) {
                console.error("Lỗi khi tạo dự án mới:", error);
                throw error;
            }
        },
        async fetchProjectById(id) {
            this.isLoading = true;
            try {
                const response = await api.get(`/projects/${id}`);
                this.currentProject = response.data;
                return response.data;
            } catch (error) {
                console.error("Lỗi khi tải thông tin dự án:", error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        }
    }
});
