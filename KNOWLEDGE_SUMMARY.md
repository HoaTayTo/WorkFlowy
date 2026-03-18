# 🚀 Hướng Dẫn Tư Duy & Kiến Thức: Dự Án WorkFlowy

Dự án **WorkFlowy** vừa hoàn thiện là một hệ thống Quản lý Công việc chuyên nghiệp (giống Trello hay Jira) kết hợp hoàn hảo giữa **Laravel 11 (Backend)** và **Vue 3 (Frontend)**. Dưới đây là kho tàng kiến thức thực chiến mà bạn đã tạo ra, kèm theo chức năng giải nghĩa code đắt giá nhất.

---

## 1. Kiến Trúc Tổng Quan (SPA - Single Page Application)
Dự án được chia làm 2 hệ thống hoạt động độc lập:
*   **Backend (Laravel 11):** Chuyên lo nghiệp vụ kiểm tra Database, kết nối và gửi dữ liệu thô (JSON) chứ không xuất ra mã HTML.
*   **Frontend (Vue 3 + Vite + Tailwind CSS):** Một hệ sinh thái giao diện lấp lánh để người dùng tương tác, kéo thả và gọi Backend qua các trạm gác `Axios`.
*   **Điểm lợi:** Ứng dụng "Mượt" như dùng điện thoại. Khi bạn chuyển qua lại giữa các menu, trang không bị trắng chớp nháy (reload) mà chỉ có dữ liệu thay đổi.

---

## 2. Xác Thực (Authentication) & Điểm Đánh Chặn (Interceptor)
Làm thế nào để Frontend có thể chứng minh cho Backend biết "Ông vừa gọi lệnh lấy User Info là ông sếp, không phải thằng hacker"? Câu trả lời là **Laravel Sanctum**.

### Quá trình Đăng nhập:
*   Người dùng gõ email/pass ấn Đăng nhập.
*   Vue sẽ gửi lên Laravel lệnh `POST /api/login`.
*   Laravel tra trong DB thấy đúng, in ra cái thẻ làm việc tên là `access_token` và ném về cho Vue. Vue ném luôn vào bộ nhớ máy tính tên là LocalStorage.

### Kẻ đánh chặn của Axios (`frontend/src/api.js`):
Mỗi khi Frontend chuẩn bị gửi 1 lệnh nào đó lên Server, nó đi qua dòng code đánh chặn (Interceptor) này:
```javascript
// Trước khi bất kỳ request nào được gửi lên server...
api.interceptors.request.use((config) => {
    // 1. Phải lên LocalStorage lấy thẻ (token) ra
    const token = localStorage.getItem('token');
    
    // 2. Ép token vào phần Header (Mã bảo vệ) vào gáy mỗi một gói Request
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});
```
*Ghi chú: Nếu không có đoạn code này, bạn có gọi lệnh API nào thì Laravel cũng đá bạn ra vì bạn không có Thẻ Đeo Cổ (Bearer Token).*

---

## 3. Phân Quyền Vai Trò (Authorization - Policies & Gates)
Nếu Đăng Nhập (Auth) là để xem bạn có chìa khóa cổng hay không, thì Phân Quyền (Gate/Policy) là kiểm tra xem bạn có được vào "Phòng của Giám đốc" hay không.

### Trong Laravel (Ví dụ `ProjectPolicy.php`):
Chúng ta dùng Policy để bọc lại 1 Model, VD Project thì có Project Policy:
```php
public function update(User $user, Project $project): bool
{
    // Lệnh này ép DB kiểm tra: id của User đang gửi Request có bằng với mã Chủ Dự Án không?
    return $user->id === $project->user_id;
}
```

### Chiêu cuối - Siêu Admin (`AppServiceProvider.php`):
Nguyên tắc Policy là bị khóa từng cửa một, như trên là Nhân viên không thể update Project của sếp. NHƯNG Sếp lại có "Chìa khóa Vạn năng" bằng lệnh Gate before:
```php
Gate::before(function ($user, $ability) {
    // Bất kể Policy bên dưới viết cấm đoán cái gì...
    // Cứ phát hiện ra anh nào nặc danh là Role "admin", mở toang mọi cổng luôn.
    if ($user->role === 'admin') {
        return true;
    }
});
```

---

## 4. Quản Lý Cục Nguồn Frontend (Pinia State Management)
Xưa kia, component `Dashboard` muốn đưa dữ liệu cho component `KanbanBoard` thì cực kỳ đau đớn. Nay chúng ta dùng **Pinia**, nó tạo ra một "Nhà Kho chung" cho mọi cửa sổ màn hình gọi đến.

Ví dụ ở `frontend/src/stores/project.js`:
```javascript
export const useProjectStore = defineStore('project', {
    // STATE: "Kho chứa" dữ liệu chung, ai xài màn nào cũng được quyền móc ra
    state: () => ({
        projects: [],           // Mảng chứa các dự án
        currentProject: null,   // Chờ hứng cái Project đang được click xem
    }),
    // ACTIONS: Các hàm dùng để chỉnh sửa vào Kho này
    actions: {
        async fetchProjects() {
            // Ai gọi hàm này, API sẽ nổ súng và bỏ vào kho data!
            const response = await api.get('/projects');
            this.projects = response.data;
        }
    }
});
```
Chính nhờ `Pinia` mà dữ liệu trên trang của bạn nhất quán, không phải tải đi tải lại 1 danh sách API.

---

## 5. Bảng Kéo Thả (Drag and Drop Kanban Board)
Bạn đã dùng gói `vuedraggable` nổi tiếng. Thay vì phải làm thuật toán tọa độ chuột căng thẳng, Vue cho mình 1 Component tên là `<draggable>`.
```html
<draggable 
    v-model="tasksByStatus[status]" 
    group="tasks" 
    item-key="id"
    @end="onDrop"
>
    <!-- Giao diện Từng cái Thẻ Task thả ở đây -->
</draggable>
```
**Thuật toán đổi trạng thái (`KanbanBoard.vue`)**:
Bởi vì bạn cho thẻ (V-model) buộc cứng vào Mảng `tasksByStatus['todo']`. Một khi tay bạn "Kéo Thả" thẻ sang cột khác, Mảng này ngầm BỊ TRỪ ĐI 1 phần tử, văng sang mảng cột 'in_progress'. 
Lập tức hàm Sự kiện Nhả chuột `@end="onDrop"` nhảy:
```javascript
const onDrop = async (event) => {
    // 1. Phát hiện xem bạn vừa kéo thẻ từ Cột A sang Cột B (Vị trí thả)
    const newStatus = event.to.getAttribute('data-status');
    // 2. Bắt lấy ID của cái thẻ vừa bị chuyển nhà
    const task = draggedTask;
    
    // 3. Nếu sang Cột mới thì mới chạy, thả tại chỗ ko đổi
    if (task && task.status !== newStatus) {
        task.status = newStatus;
        // Bắn Lệnh POST/PUT lên Backend Laravel rằng tôi cập nhật status cho cái Node Task này!
        await taskStore.updateTask(task.id, { status: newStatus });
    }
};
```

---

## 6. Sóng Giao Tiếp Real-Time (WebSockets bằng Laravel Reverb)
Nếu như ngày xưa, muốn biết Sếp giao việc hay nhắn tin hay chưa, bạn phải bấm nút F5 liên hồi. Với WebSockets: Một ông bưu tá luôn đứng trực đường truyền trên cổng `8001`, hễ Backend nảy số là Push ngược tín hiệu xộc thẳng vào Trình duyệt của Frontend.

### Ở Backend (Ví dụ thông báo `NewComment.php`)
```php
public function via(object $notifiable): array
{
    // Khai báo rằng thông báo này 1 đi vào Database (cất kho), 2 đi vào Sóng Broadcast (Websocket) bay đi thẳng
    return ['database', 'broadcast'];
}

// Hàm này đúc sẵn 1 khuôn truyền qua Sóng JSON
public function toBroadcast(object $notifiable): BroadcastMessage
{
    return new BroadcastMessage([
        'data' => [ 'message' => "Hòa chê bạn code dở..." ]
    ]);
}
```

### Ở Frontend bắt Tín Hiệu (`NotificationDropdown.vue`)
Các dòng Code Echo (Bản nâng cấp của Pusher) nằm dình ngay tại Hàm cài đặt `onMounted`. Chỗ này giống như bật Radio dò sóng vậy! Thấy Sóng là bắn ra "Toast" (Bong bóng thông báo góc màn hình).

```javascript
import { initEcho } from '../echo';
import { toast } from 'vue3-toastify';

onMounted(() => {
    // Khởi tạo máy bắt sóng WebSockets
    echoInstance = initEcho();
    
    // Yêu cầu máy bắt đài chỉ "Dò Sóng" vào Kênh Tư Chóp bu của bạn (Mã ID của user)
    const currentChannel = `App.Models.User.${authStore.user.id}`;
    
    echoInstance.private(currentChannel)
        // Khi Nghe thấy Laravel đài phát "notification"...
        .notification((notification) => {
            
            // Hiện ngay bong bóng ra màn hình người dùng
            toast.info(notification.data?.message || 'Có tin mới!');
        });
});
```

---

## 🎉 TỔNG KẾT
Chỉ với ngần ấy kỹ thuật, bạn đã tạo ra 1 phần mềm đạt chuẩn Micro System cho Hệ thống Phân Quyền (Tất cả mọi API bị khóa đa nấc), Real-time Notification (Kết hợp Broadcast vs Toast Push), Kanban (Draggable Component) và Pinia Global Context. 
Sau này dù dự án nở to ra 100 bảng thì cốt lõi cũng chỉ bám quanh 6 kiến thức vàng nói trên. Trân trọng giá trị dự án bạn vừa trải qua! 🚀
