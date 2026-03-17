<template>
    <div class="h-screen bg-gray-50 flex flex-col pt-[72px]">
        <!-- Navbar phụ của riêng Project -->
        <header class="bg-white shadow z-10 fixed w-full top-0 h-[72px]">
            <div class="max-w-[1440px] w-full mx-auto py-4 px-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button @click="router.push({ name: 'dashboard' })" class="text-gray-500 hover:text-indigo-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">{{ projectStore.currentProject?.name || 'Đang tải...' }}</h1>
                        <p class="text-xs text-gray-500">{{ projectStore.currentProject?.description }}</p>
                    </div>
                </div>
                <!-- Nút thêm Task -->
                <button v-if="canCreateTask" @click="showTaskModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium text-sm transition shadow-sm flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Thêm Công Việc</span>
                </button>
            </div>
        </header>

        <!-- Thân Bảng Kanban (Có thanh cuộn ngang nếu màn hình nhỏ) -->
        <main class="flex-1 overflow-x-auto overflow-y-hidden p-6 relative">
            <div v-if="taskStore.isLoading" class="absolute inset-0 flex items-center justify-center bg-white/50 z-20">
                <span class="text-indigo-600 font-medium">Đang tải dữ liệu...</span>
            </div>

            <div class="flex space-x-6 h-full items-start min-w-max">
                <!-- Cột: TO DO -->
                <div class="w-80 bg-gray-100 rounded-lg shadow-sm border border-gray-200 flex flex-col max-h-full"
                    @dragover.prevent
                    @drop="onDrop($event, 'todo')">
                    <div class="p-3 border-b border-gray-200 bg-white rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold text-gray-700">TO DO</h3>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full font-bold">{{ taskStore.todoTasks.length }}</span>
                    </div>
                    <div class="p-3 flex-1 overflow-y-auto space-y-3">
                        <div v-for="task in taskStore.todoTasks" :key="task.id" :draggable="!isAdmin" @dragstart="onDragStart($event, task.id)"
                            :class="{'cursor-grab hover:shadow-md': !isAdmin, 'cursor-default': isAdmin}"
                            class="bg-white p-4 rounded shadow-sm border border-gray-200 transition">
                            <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center mt-3">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <div class="flex items-center space-x-3">
                                    <div v-if="task.assignee" class="h-6 w-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold" :title="'Được giao cho: ' + task.assignee.name">
                                        {{ task.assignee.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <button @click="openTaskDetails(task)" class="text-indigo-400 hover:text-indigo-600" title="Bình luận/Chi tiết">💬</button>
                                    <button v-if="canCreateTask" @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột: IN PROGRESS -->
                <div class="w-80 bg-blue-50/50 rounded-lg shadow-sm border border-blue-100 flex flex-col max-h-full"
                    @dragover.prevent
                    @drop="onDrop($event, 'in_progress')">
                    <div class="p-3 border-b border-blue-200 bg-white rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold text-blue-700">IN PROGRESS</h3>
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full font-bold">{{ taskStore.inProgressTasks.length }}</span>
                    </div>
                    <div class="p-3 flex-1 overflow-y-auto space-y-3">
                        <div v-for="task in taskStore.inProgressTasks" :key="task.id" :draggable="!isAdmin" @dragstart="onDragStart($event, task.id)"
                            :class="{'cursor-grab hover:shadow-md': !isAdmin, 'cursor-default': isAdmin}"
                            class="bg-white p-4 rounded shadow-sm border border-blue-200 transition">
                            <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center mt-3">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <div class="flex items-center space-x-3">
                                    <div v-if="task.assignee" class="h-6 w-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold" :title="'Được giao cho: ' + task.assignee.name">
                                        {{ task.assignee.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <button @click="openTaskDetails(task)" class="text-indigo-400 hover:text-indigo-600" title="Bình luận/Chi tiết">💬</button>
                                    <button v-if="canCreateTask" @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột: REVIEW -->
                <div class="w-80 bg-orange-50/50 rounded-lg shadow-sm border border-orange-100 flex flex-col max-h-full"
                    @dragover.prevent
                    @drop="onDrop($event, 'review')">
                    <div class="p-3 border-b border-orange-200 bg-white rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold text-orange-700">REVIEW</h3>
                        <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full font-bold">{{ taskStore.reviewTasks.length }}</span>
                    </div>
                    <div class="p-3 flex-1 overflow-y-auto space-y-3">
                        <div v-for="task in taskStore.reviewTasks" :key="task.id" :draggable="!isAdmin" @dragstart="onDragStart($event, task.id)"
                            :class="{'cursor-grab hover:shadow-md': !isAdmin, 'cursor-default': isAdmin}"
                            class="bg-white p-4 rounded shadow-sm border border-orange-200 transition">
                            <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center mt-3">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <div class="flex items-center space-x-3">
                                    <div v-if="task.assignee" class="h-6 w-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold" :title="'Được giao cho: ' + task.assignee.name">
                                        {{ task.assignee.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <button @click="openTaskDetails(task)" class="text-indigo-400 hover:text-indigo-600" title="Bình luận/Chi tiết">💬</button>
                                    <button v-if="canCreateTask" @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột: DONE -->
                <div class="w-80 bg-green-50/50 rounded-lg shadow-sm border border-green-100 flex flex-col max-h-full"
                    @dragover.prevent
                    @drop="onDrop($event, 'done')">
                    <div class="p-3 border-b border-green-200 bg-white rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold text-green-700">DONE</h3>
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-bold">{{ taskStore.doneTasks.length }}</span>
                    </div>
                    <div class="p-3 flex-1 overflow-y-auto space-y-3">
                        <div v-for="task in taskStore.doneTasks" :key="task.id" :draggable="!isAdmin" @dragstart="onDragStart($event, task.id)"
                            :class="{'cursor-grab hover:shadow-md': !isAdmin, 'cursor-default': isAdmin}"
                            class="bg-white p-4 rounded shadow-sm border border-green-200 transition opacity-75 hover:opacity-100">
                            <h4 class="font-medium text-gray-900 mb-1 line-through">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center mt-3">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <div class="flex items-center space-x-3">
                                    <div v-if="task.assignee" class="h-6 w-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold" :title="'Được giao cho: ' + task.assignee.name">
                                        {{ task.assignee.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <button @click="openTaskDetails(task)" class="text-indigo-400 hover:text-indigo-600" title="Bình luận/Chi tiết">💬</button>
                                    <button v-if="canCreateTask" @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Thêm Task Modal -->
        <div v-if="showTaskModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-[400px] shadow-xl">
                <h3 class="text-lg font-bold mb-4 text-gray-900">Thêm Công Việc Mới</h3>
                <form @submit.prevent="handleCreateTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên công việc</label>
                        <input v-model="newTask.title" type="text" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mô tả chi tiết</label>
                        <textarea v-model="newTask.description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Người thực hiện</label>
                        <select v-model="newTask.assignee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option :value="null">Không giao cho ai</option>
                            <option v-for="u in usersList" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mức độ ưu tiên</label>
                        <select v-model="newTask.priority" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="low">Thấp (Low)</option>
                            <option value="medium">Trung bình (Medium)</option>
                            <option value="high">Cao (High)</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="showTaskModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition shadow-sm">Thêm</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Chi Tiết Task & Xử lý Bình Luận Modal -->
        <div v-if="showDetailsModal && selectedTask" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl shadow-xl flex flex-col max-h-[90vh]">
                <!-- Header Modal -->
                <div class="flex justify-between items-start mb-4 border-b pb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ selectedTask.title }}</h3>
                        <p class="text-sm text-gray-500 mt-1 whitespace-pre-wrap">{{ selectedTask.description || 'Không có mô tả' }}</p>
                        <div v-if="selectedTask.assignee" class="mt-3 flex items-center space-x-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Người thực hiện:</span>
                            <div class="flex items-center space-x-2 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">
                                <div class="h-5 w-5 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px] font-bold">
                                    {{ selectedTask.assignee.name.charAt(0).toUpperCase() }}
                                </div>
                                <span class="text-sm font-medium text-indigo-700">{{ selectedTask.assignee.name }}</span>
                            </div>
                        </div>
                    </div>
                    <button @click="closeTaskDetails" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <!-- List Comments (Có thanh cuộn) -->
                <div class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">
                    <h4 class="font-semibold text-gray-700 sticky top-0 bg-white py-2">Bình luận ({{ taskComments.length }})</h4>
                    
                    <div v-if="isLoadingComments" class="text-center text-sm text-gray-500 py-4">Đang tải bình luận...</div>
                    <div v-else-if="taskComments.length === 0" class="text-center text-sm text-gray-400 py-4 italic">Chưa có bình luận nào.</div>
                    
                    <!-- Hiển thị từng bình luận -->
                    <div v-for="comment in taskComments" :key="comment.id" class="bg-gray-50 rounded-lg p-3 relative group">
                        <div class="flex justify-between items-start">
                            <span class="font-medium text-sm text-indigo-700">{{ comment.user?.name || 'Ai đó' }}</span>
                            <span class="text-xs text-gray-400">{{ new Date(comment.created_at).toLocaleString('vi-VN') }}</span>
                        </div>
                        <p class="text-gray-700 text-sm mt-1 whitespace-pre-wrap">{{ comment.content }}</p>
                        
                        <!-- Nút Xóa Comment (hiện khi thẻ bị hover) -->
                        <button v-if="canDeleteComment(comment.user_id)" @click="deleteComment(comment.id)" 
                            class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 bg-white rounded-full p-1 shadow-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Input viết bình luận mới -->
                <div class="mt-4 border-t pt-4">
                    <form @submit.prevent="submitComment" class="flex gap-2">
                        <input v-model="newComment" type="text" placeholder="Viết bình luận..." required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                        <button type="submit" :disabled="isSubmittingComment"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition disabled:opacity-50 text-sm font-medium">
                            Gửi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '../stores/project';
import { useTaskStore } from '../stores/task';
import { useAuthStore } from '../stores/auth';
import api from '../api';

// Prop id được Vue Router tự động ném vào (đã bật props: true)
const props = defineProps({
    id: {
        type: String,
        required: true
    }
});

const router = useRouter();
const projectStore = useProjectStore();
const taskStore = useTaskStore();
const authStore = useAuthStore();

const showTaskModal = ref(false);
const newTask = ref({
    title: '',
    description: '',
    priority: 'medium',
    assignee_id: null,
});

const usersList = ref([]);

// State quản lý việc xem chi tiết và Comment
const showDetailsModal = ref(false);
const selectedTask = ref(null);
const taskComments = ref([]);
const newComment = ref('');
const isLoadingComments = ref(false);
const isSubmittingComment = ref(false);

// Kiểm tra quyền: Chỉ chủ dự án HOẶC Admin mới được Tạo mới / Xóa Task
const canCreateTask = computed(() => {
    if (!projectStore.currentProject || !authStore.user) return false;
    return authStore.user.role === 'admin' || projectStore.currentProject.user_id === authStore.user.id;
});

const isAdmin = computed(() => authStore.user?.role === 'admin');

// Load dữ liệu khi lên màn hình
onMounted(async () => {
    // Kéo thông tin dự án hiện tại
    await projectStore.fetchProjectById(props.id);
    // Kéo tất cả tasks của dự án hiện tại
    await taskStore.fetchTasks(props.id);
    
    // Lấy danh sách toàn bộ User để cho phép "Giao Việc" (Thực ra cũng chỉ Admin mới cần)
    if (isAdmin.value) {
        try {
            const response = await api.get('/users');
            usersList.value = response.data;
        } catch (e) {
            console.error("Lỗi lấy danh sách user:", e);
        }
    }
});

// Logic Kéo - Thả (Drag and Drop HTML5)
const onDragStart = (event, taskId) => {
    if (isAdmin.value) {
        event.preventDefault();
        return;
    }
    event.dataTransfer.dropEffect = 'move';
    event.dataTransfer.effectAllowed = 'move';
    // Đính kèm ID của task vào sự kiện di chuột
    event.dataTransfer.setData('taskId', taskId);
};

const onDrop = async (event, newStatus) => {
    if (isAdmin.value) return;
    
    // Lấy ID của task đã lưu lúc nãy
    const taskId = event.dataTransfer.getData('taskId');
    if (taskId) {
        // Cập nhật trạng thái sang cột mới
        await taskStore.updateTaskStatus(parseInt(taskId), newStatus);
    }
};

// Xử lý Form
const handleCreateTask = async () => {
    if (!newTask.value.title) return;
    
    await taskStore.addTask({
        project_id: props.id,
        title: newTask.value.title,
        description: newTask.value.description,
        priority: newTask.value.priority,
        assignee_id: newTask.value.assignee_id,
        status: 'todo' // Mặc định ở cột Đầu Tiên
    });
    
    // Tải lại list tasks nới chứa cả `assignee` bị giấu nếu cần (Store của mình tự trả về task sau khi tạo ko load assignee)
    // Tốt nhất là refetch lại nhẹ 1 lần hoặc store tự config
    await taskStore.fetchTasks(props.id);

    // Đóng popup & reset form
    showTaskModal.value = false;
    newTask.value = { title: '', description: '', priority: 'medium', assignee_id: null };
};

const deleteTask = async (id) => {
    if (confirm("Bạn có chắc muốn xóa công việc này không?")) {
        await taskStore.deleteTask(id);
    }
};

// Các hàm tiện ích chuyển đổi chữ & màu sắc
const getPriorityText = (priority) => {
    const map = { low: 'Low', medium: 'Medium', high: 'High' };
    return map[priority] || priority;
};

const getPriorityClass = (priority) => {
    switch (priority) {
        case 'high': return 'bg-red-100 text-red-700 border border-red-200';
        case 'medium': return 'bg-yellow-100 text-yellow-700 border border-yellow-200';
        case 'low': return 'bg-blue-100 text-blue-700 border border-blue-200';
        default: return 'bg-gray-100 text-gray-700';
    }
};

// ================= LOGIC BÌNH LUẬN & CHI TIẾT TASK =================
const openTaskDetails = async (task) => {
    selectedTask.value = task;
    showDetailsModal.value = true;
    taskComments.value = []; // reset cũ
    
    // Gọi API lấy comment
    isLoadingComments.value = true;
    try {
        const response = await api.get(`/tasks/${task.id}/comments`);
        taskComments.value = response.data;
    } catch (error) {
        console.error("Lỗi lấy bình luận:", error);
    } finally {
        isLoadingComments.value = false;
    }
};

const closeTaskDetails = () => {
    showDetailsModal.value = false;
    selectedTask.value = null;
    newComment.value = '';
};

const submitComment = async () => {
    if (!newComment.value.trim() || !selectedTask.value) return;
    
    isSubmittingComment.value = true;
    try {
        const response = await api.post(`/tasks/${selectedTask.value.id}/comments`, {
            content: newComment.value
        });
        // Nhồi bình luận mới vào danh sách hiện tại để UI tự update
        taskComments.value.push(response.data);
        newComment.value = ''; // xóa ô nhập
    } catch (error) {
        console.error("Lỗi đăng bình luận:", error);
    } finally {
        isSubmittingComment.value = false;
    }
};

const canDeleteComment = (commentUserId) => {
    // Chỉ cho phép xóa nếu là chủ nhân comment, HOẶC user đang login có role là admin
    return commentUserId === authStore.user?.id || authStore.user?.role === 'admin';
};

const deleteComment = async (commentId) => {
    if (!confirm("Xóa bình luận này?")) return;
    
    try {
        await api.delete(`/comments/${commentId}`);
        // Lọc khỏi mảng để UI tự gạch bỏ
        taskComments.value = taskComments.value.filter(c => c.id !== commentId);
    } catch (error) {
        console.error("Lỗi xóa bình luận:", error);
    }
};
</script>
