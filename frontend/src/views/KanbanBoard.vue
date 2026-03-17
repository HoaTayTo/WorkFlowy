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
                <button @click="showTaskModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium text-sm transition shadow-sm flex items-center space-x-2">
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
                        <div v-for="task in taskStore.todoTasks" :key="task.id" draggable="true" @dragstart="onDragStart($event, task.id)"
                            class="bg-white p-4 rounded shadow-sm border border-gray-200 cursor-grab hover:shadow-md transition">
                            <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <button @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
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
                        <div v-for="task in taskStore.inProgressTasks" :key="task.id" draggable="true" @dragstart="onDragStart($event, task.id)"
                            class="bg-white p-4 rounded shadow-sm border border-blue-200 cursor-grab hover:shadow-md transition">
                            <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <button @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
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
                        <div v-for="task in taskStore.reviewTasks" :key="task.id" draggable="true" @dragstart="onDragStart($event, task.id)"
                            class="bg-white p-4 rounded shadow-sm border border-orange-200 cursor-grab hover:shadow-md transition">
                            <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <button @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
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
                        <div v-for="task in taskStore.doneTasks" :key="task.id" draggable="true" @dragstart="onDragStart($event, task.id)"
                            class="bg-white p-4 rounded shadow-sm border border-green-200 cursor-grab hover:shadow-md transition opacity-75 hover:opacity-100">
                            <h4 class="font-medium text-gray-900 mb-1 line-through">{{ task.title }}</h4>
                            <p class="text-xs text-gray-500 mb-3">{{ task.description }}</p>
                            <div class="flex justify-between items-center">
                                <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-1 rounded-md font-medium">{{ getPriorityText(task.priority) }}</span>
                                <button @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600" title="Xóa">X</button>
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
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectStore } from '../stores/project';
import { useTaskStore } from '../stores/task';

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

const showTaskModal = ref(false);
const newTask = ref({
    title: '',
    description: '',
    priority: 'medium',
});

// Load dữ liệu khi lên màn hình
onMounted(async () => {
    // Kéo thông tin dự án hiện tại
    await projectStore.fetchProjectById(props.id);
    // Kéo tất cả tasks của dự án hiện tại
    await taskStore.fetchTasks(props.id);
});

// Logic Kéo - Thả (Drag and Drop HTML5)
const onDragStart = (event, taskId) => {
    event.dataTransfer.dropEffect = 'move';
    event.dataTransfer.effectAllowed = 'move';
    // Đính kèm ID của task vào sự kiện di chuột
    event.dataTransfer.setData('taskId', taskId);
};

const onDrop = async (event, newStatus) => {
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
        status: 'todo' // Mặc định ở cột Đầu Tiên
    });
    
    // Đóng popup & reset form
    showTaskModal.value = false;
    newTask.value = { title: '', description: '', priority: 'medium' };
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
</script>
