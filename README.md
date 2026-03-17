<div align="center">
  <h1 align="center">🎯 WorkFlowy - Kanban Task Management</h1>
  <p align="center">
    A robust, scalable Kanban-style project management tool inspired by Trello & Jira.
    <br />
    Built with <strong>Laravel 11 RESTful API</strong> + <strong>Vue.js 3 (Composition API)</strong>.
  </p>
</div>

---

## 🚀 Màn Chào Sân (Introduction)

**WorkFlowy** được thiết kế để giải quyết bài toán quản lý công việc nhóm một cách trực quan, hiện đại và chuẩn mực. Thông qua mô hình bảng Kanban (Kéo thả mượt mà), dự án này là minh chứng rõ ràng cho việc nắm vững cơ sở dữ liệu quan hệ, tích hợp hệ thống qua RESTful API, quản lý State trên Frontend (Pinia) và hiểu biết sâu sắc về các luồng Authorization (Bảo mật 2 lớp).

Dự án được triển khai bằng bộ quy chuẩn **Git Flow**, áp dụng mô hình kiến trúc Tách Rời (Decoupled Architecture - SPA kết nối Backend API qua Axios interceptors).

---

## 🛠️ Công Nghệ Chuyên Sâu Đã Sử Dụng (Tech Stack)

### 🔹 Backend (Core API)
- **Framework:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL relational DB (Thiết kế Schema 1-N phức tạp)
- **Authentication:** Laravel Sanctum (Cấp phát Token API, JWT-like)
- **Authorization:** Laravel Policies & Gates (Bảo vệ quyền truy cập tài nguyên của User)
- **Architecture:** Chuẩn RESTful API (Resource Routing, Validation Request riêng biệt)

### 🔹 Frontend (User Interface)
- **Framework:** Vue.js 3 (Composition API - `setup` script)
- **State Management:** Pinia (Kho lưu trữ siêu nhẹ và tối ưu hơn Vuex)
- **Routing:** Vue Router (Với Navigation Guards chặn truy cập trái phép)
- **API Client:** Axios interceptors (Tự động đính kèm Bearer Token ngầm hóa)
- **Styling UI:** Tailwind CSS v3 (Utility-first, responsive grid/flex layout)
- **Interactions:** HTML5 Drag and Drop API cho thao tác di chuyển thẻ trực quan (Optimistic UI Update)

---

## ✨ Điểm Sáng Kỹ Thuật (Key Features)

✅ **Hệ thống Token API hoàn chỉnh:** Frontend và Backend hoàn toàn độc lập. Việc xác thực danh tính dựa hoàn toàn vào chuỗi Access Token, dễ dàng nâng cấp sang React Native cho Mobile App trong tương lai.
<br>✅ **Bảo mật nhiều lớp (Security in depth):**
   - Lớp 1: Middleware Auth Sanctum chặn người đứng ngoài ứng dụng.
   - Lớp 2: Laravel Policies ngăn một User (dù đã Login) cố tình truy cập/xóa/sửa Project hoặc Task thuộc về một User khác.
<br>✅ **Giao diện đa nhiệm (SPA & Tối ưu RAM):** Mọi thao tác đổi màn hình, Thêm/Sửa/Kéo/Thả diễn ra trong chớp mắt mà không bao giờ tải lại trang (Zero page reloads).

---

## 🗄️ Cấu trúc Dữ Liệu (Database Schema)

- **`users`** (Tài khoản người dùng)
- **`projects`** (Bảng dự án): Belongs to 1 User.
- **`tasks`** (Bảng thẻ công việc): Belongs to 1 Project.
  - Phân loại bằng ENUM Priority: `low`, `medium`, `high`.
  - Phân loại bằng ENUM Status (Cột bảng Kanban): `todo`, `in_progress`, `review`, `done`.

---

## ⚙️ Hướng Dẫn Cài Đặt và Chạy Thử (Run Locally)

### 1. Khởi động Backend Laravel
```bash
# Cài đặt thư viện PHP
composer install

# Copy biến môi trường
cp .env.example .env

# Chỉnh sửa file .env để kết nối với MySQL của bạn
DB_DATABASE=workflowy_db
DB_USERNAME=root

# Sinh khóa bí mật của Laravel
php artisan key:generate

# Chạy tạo bảng CSDL
php artisan migrate

# Bật máy chủ Backend (Mặc định ở http://127.0.0.1:8000)
php artisan serve
```

### 2. Khởi động Frontend Vue / Vite
```bash
cd frontend

# Cài đặt thư viện JS
npm install

# Bật giao diện Frontend (Mặc định ở http://localhost:5173)
npm run dev
```

---
*Dự án thực chiến được xây dựng và commit nghiêm ngặt theo mô hình GitFlow để rèn luyện tư duy làm việc nhóm (Agile/Scrum).*
