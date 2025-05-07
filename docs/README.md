# QLNhanSu - Hệ thống quản lý nhân sự

Hệ thống quản lý nhân sự được xây dựng bằng Node.js và Express.js, sử dụng MySQL làm cơ sở dữ liệu.

## Tính năng chính

- Quản lý thông tin nhân viên
- Quản lý phòng ban
- Quản lý chức vụ
- Quản lý hợp đồng
- Quản lý phúc lợi
- Quản lý đào tạo
- Quản lý tài liệu
- Quản lý chứng chỉ
- Quản lý kỹ năng
- Quản lý kinh nghiệm
- Quản lý thông báo
- Quản lý người dùng
- Xác thực và phân quyền
- API RESTful

## Yêu cầu hệ thống

- Node.js >= 14.0.0
- MySQL >= 5.7
- Redis >= 6.0

## Cài đặt

1. Clone repository:
```bash
git clone https://github.com/your-username/QLNhanSu.git
cd QLNhanSu
```

2. Cài đặt dependencies:
```bash
npm install
```

3. Tạo file `.env` từ `.env.example`:
```bash
cp .env.example .env
```

4. Cập nhật các biến môi trường trong file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qlnhansu
DB_USERNAME=root
DB_PASSWORD=your_password

JWT_SECRET=your_jwt_secret_key
```

5. Tạo cơ sở dữ liệu và chạy migrations:
```bash
npm run db:create
npm run db:migrate
```

6. Chạy seeds để tạo dữ liệu mẫu:
```bash
npm run db:seed
```

7. Khởi động ứng dụng:
```bash
npm start
```

## Cấu trúc dự án

```
QLNhanSu/
├── src/
│   ├── config/         # Cấu hình ứng dụng
│   ├── controllers/    # Controllers
│   ├── middlewares/    # Middlewares
│   ├── models/         # Models
│   ├── routes/         # Routes
│   ├── services/       # Services
│   ├── utils/          # Utilities
│   └── index.js        # Entry point
├── tests/              # Tests
├── uploads/            # Thư mục uploads
├── .env.example        # Mẫu file cấu hình
├── .gitignore         # Git ignore
├── package.json        # Dependencies
└── README.md          # Documentation
```

## API Documentation

API documentation có sẵn tại `/api-docs` khi chạy ứng dụng.

## Testing

```bash
# Chạy tất cả tests
npm test

# Chạy tests với coverage
npm run test:coverage

# Chạy tests trong watch mode
npm run test:watch
```

## Development

```bash
# Chạy ứng dụng trong development mode
npm run dev

# Lint code
npm run lint

# Format code
npm run format
```

## License

MIT

# Hệ Thống Quản Lý Nhân Sự

## Cấu Trúc Cơ Sở Dữ Liệu

### 1. Bảng users (Người dùng)
- **Các trường chính**: 
  - user_id: ID người dùng
  - username: Tên đăng nhập
  - email: Email
  - password_hash: Mật khẩu đã mã hóa
  - role_id: ID vai trò
  - department_id: ID phòng ban
  - position_id: ID chức vụ
- **Trường hợp sử dụng**: Quản lý thông tin đăng nhập, phân quyền và thông tin cơ bản của nhân viên

### 2. Bảng user_profiles (Hồ sơ người dùng)
- **Các trường chính**:
  - profile_id: ID hồ sơ
  - user_id: ID người dùng
  - full_name: Họ tên đầy đủ
  - avatar_url: Đường dẫn ảnh đại diện
  - date_of_birth: Ngày sinh
  - gender: Giới tính
  - phone_number: Số điện thoại
- **Trường hợp sử dụng**: Lưu trữ thông tin chi tiết về nhân viên như thông tin cá nhân, liên hệ

### 3. Bảng departments (Phòng ban)
- **Các trường chính**:
  - id: ID phòng ban
  - name: Tên phòng ban
  - description: Mô tả
  - manager_id: ID người quản lý
- **Trường hợp sử dụng**: Quản lý cấu trúc tổ chức, phân chia phòng ban

### 4. Bảng positions (Chức vụ)
- **Các trường chính**:
  - id: ID chức vụ
  - name: Tên chức vụ
  - description: Mô tả
  - department_id: ID phòng ban
- **Trường hợp sử dụng**: Quản lý các vị trí công việc trong từng phòng ban

### 5. Bảng employee_positions (Vị trí nhân viên)
- **Các trường chính**:
  - id: ID vị trí
  - employee_id: ID nhân viên
  - position_id: ID chức vụ
  - start_date: Ngày bắt đầu
  - end_date: Ngày kết thúc
- **Trường hợp sử dụng**: Theo dõi lịch sử vị trí công việc của nhân viên

### 6. Bảng leaves (Nghỉ phép)
- **Các trường chính**:
  - id: ID đơn nghỉ
  - employee_id: ID nhân viên
  - leave_type: Loại nghỉ
  - start_date: Ngày bắt đầu
  - end_date: Ngày kết thúc
  - status: Trạng thái
- **Trường hợp sử dụng**: Quản lý đơn xin nghỉ phép của nhân viên

### 7. Bảng performances (Đánh giá hiệu suất)
- **Các trường chính**:
  - id: ID đánh giá
  - employee_id: ID nhân viên
  - reviewer_id: ID người đánh giá
  - performance_score: Điểm đánh giá
  - strengths: Điểm mạnh
  - weaknesses: Điểm yếu
- **Trường hợp sử dụng**: Đánh giá và theo dõi hiệu suất làm việc của nhân viên

### 8. Bảng trainings (Đào tạo)
- **Các trường chính**:
  - id: ID khóa đào tạo
  - name: Tên khóa học
  - description: Mô tả
  - start_date: Ngày bắt đầu
  - end_date: Ngày kết thúc
  - location: Địa điểm
- **Trường hợp sử dụng**: Quản lý các khóa đào tạo

### 9. Bảng employee_trainings (Đào tạo nhân viên)
- **Các trường chính**:
  - id: ID ghi nhận
  - employee_id: ID nhân viên
  - training_id: ID khóa đào tạo
  - status: Trạng thái
  - result: Kết quả
- **Trường hợp sử dụng**: Theo dõi việc tham gia đào tạo của nhân viên

### 10. Bảng documents (Tài liệu)
- **Các trường chính**:
  - id: ID tài liệu
  - title: Tiêu đề
  - file_url: Đường dẫn file
  - document_type: Loại tài liệu
  - uploaded_by: Người tải lên
- **Trường hợp sử dụng**: Quản lý tài liệu liên quan đến nhân sự

### 11. Bảng notifications (Thông báo)
- **Các trường chính**:
  - id: ID thông báo
  - user_id: ID người dùng
  - title: Tiêu đề
  - message: Nội dung
  - type: Loại thông báo
  - is_read: Đã đọc
- **Trường hợp sử dụng**: Gửi thông báo đến nhân viên

### 12. Bảng tasks (Công việc)
- **Các trường chính**:
  - id: ID công việc
  - title: Tiêu đề
  - assigned_to: Giao cho
  - assigned_by: Giao bởi
  - due_date: Hạn chót
  - status: Trạng thái
- **Trường hợp sử dụng**: Phân công và theo dõi công việc

### 13. Bảng holidays (Ngày nghỉ lễ)
- **Các trường chính**:
  - id: ID ngày nghỉ
  - name: Tên ngày nghỉ
  - date: Ngày
  - is_recurring: Lặp lại hàng năm
- **Trường hợp sử dụng**: Quản lý ngày nghỉ lễ

### 14. Bảng work_schedules (Lịch làm việc)
- **Các trường chính**:
  - id: ID lịch
  - employee_id: ID nhân viên
  - work_date: Ngày làm việc
  - start_time: Giờ bắt đầu
  - end_time: Giờ kết thúc
- **Trường hợp sử dụng**: Quản lý lịch làm việc của nhân viên

### 15. Bảng equipment_assignments (Phân công thiết bị)
- **Các trường chính**:
  - id: ID phân công
  - equipment_name: Tên thiết bị
  - employee_id: ID nhân viên
  - assigned_date: Ngày phân công
- **Trường hợp sử dụng**: Theo dõi việc phân công thiết bị cho nhân viên

### 16. Bảng rate_limits (Giới hạn truy cập)
- **Các trường chính**:
  - id: ID giới hạn
  - ip_address: Địa chỉ IP
  - endpoint: Điểm cuối
  - request_count: Số lượng yêu cầu
- **Trường hợp sử dụng**: Quản lý giới hạn truy cập API

### 17. Bảng password_reset_tokens (Token đặt lại mật khẩu)
- **Các trường chính**:
  - id: ID token
  - user_id: ID người dùng
  - token: Token
  - expires_at: Thời gian hết hạn
- **Trường hợp sử dụng**: Xử lý yêu cầu đặt lại mật khẩu

### 18. Bảng email_verification_tokens (Token xác thực email)
- **Các trường chính**:
  - id: ID token
  - user_id: ID người dùng
  - token: Token
  - expires_at: Thời gian hết hạn
- **Trường hợp sử dụng**: Xác thực email người dùng

### 19. Bảng login_attempts (Ghi nhận đăng nhập)
- **Các trường chính**:
  - id: ID ghi nhận
  - ip_address: Địa chỉ IP
  - attempt_time: Thời gian thử
- **Trường hợp sử dụng**: Theo dõi các lần đăng nhập thất bại

### 20. Bảng attendance (Chấm công)
- **Các trường chính**:
  - attendance_id: ID chấm công
  - user_id: ID người dùng
  - attendance_date: Ngày chấm công
  - recorded_at: Thời gian ghi nhận
- **Trường hợp sử dụng**: Quản lý chấm công hàng ngày

### 21. Bảng audit_logs (Nhật ký hoạt động)
- **Các trường chính**:
  - log_id: ID nhật ký
  - user_id: ID người dùng
  - action_type: Loại hành động
  - target_entity: Đối tượng
- **Trường hợp sử dụng**: Ghi lại các hoạt động trong hệ thống

### 22. Bảng bonuses (Thưởng)
- **Các trường chính**:
  - bonus_id: ID thưởng
  - user_id: ID người dùng
  - bonus_type: Loại thưởng
  - amount: Số tiền
- **Trường hợp sử dụng**: Quản lý thưởng cho nhân viên

### 23. Bảng degrees (Bằng cấp)
- **Các trường chính**:
  - degree_id: ID bằng cấp
  - user_id: ID người dùng
  - degree_name: Tên bằng cấp
  - issue_date: Ngày cấp
- **Trường hợp sử dụng**: Quản lý bằng cấp của nhân viên

### 24. Bảng family_members (Thành viên gia đình)
- **Các trường chính**:
  - family_member_id: ID thành viên
  - profile_id: ID hồ sơ
  - member_name: Tên thành viên
  - relationship: Quan hệ
- **Trường hợp sử dụng**: Lưu trữ thông tin thành viên gia đình nhân viên

### 25. Bảng payroll (Bảng lương)
- **Các trường chính**:
  - payroll_id: ID bảng lương
  - user_id: ID người dùng
  - payroll_month: Tháng lương
  - total_salary: Tổng lương
- **Trường hợp sử dụng**: Quản lý lương của nhân viên

### 26. Bảng roles (Vai trò)
- **Các trường chính**:
  - role_id: ID vai trò
  - role_name: Tên vai trò
  - description: Mô tả
- **Trường hợp sử dụng**: Quản lý phân quyền trong hệ thống

### 27. Bảng salary_history (Lịch sử lương)
- **Các trường chính**:
  - salary_history_id: ID lịch sử
  - user_id: ID người dùng
  - effective_date: Ngày hiệu lực
  - salary_coefficient: Hệ số lương
- **Trường hợp sử dụng**: Theo dõi lịch sử thay đổi lương

## Tổng Quan

Hệ thống quản lý nhân sự bao gồm 27 bảng, mỗi bảng phục vụ một chức năng cụ thể trong hệ thống. Các bảng được thiết kế với các mối quan hệ rõ ràng thông qua các khóa ngoại, đảm bảo tính toàn vẹn dữ liệu và hỗ trợ đầy đủ các chức năng quản lý nhân sự từ cơ bản đến nâng cao.

Các chức năng chính bao gồm:
- Quản lý thông tin nhân viên
- Quản lý phòng ban và chức vụ
- Quản lý nghỉ phép
- Đánh giá hiệu suất
- Đào tạo và phát triển
- Quản lý tài liệu
- Quản lý lương và thưởng
- Chấm công
- Phân công công việc
- Quản lý thiết bị
- Bảo mật và xác thực

# Quản lý Nhân sự - Hướng dẫn tối ưu

## Cấu trúc thư mục

```
.
├── api/                  # API endpoints
├── assets/              # Static assets (CSS, JS, images)
├── config/              # Configuration files
│   └── credentials.json # Google Drive API credentials
├── controllers/         # Controller logic
├── logs/               # Application logs
│   ├── access/         # Access logs
│   ├── error/          # Error logs
│   └── upload/         # Upload logs
├── models/             # Database models
├── services/           # Business logic services
├── temp/              # Temporary files
├── uploads/           # Upload directory
│   └── temp/          # Temporary upload files
└── vendor/            # PHP dependencies
```

## Tối ưu hóa

### 1. Quản lý Dependencies

- Chỉ giữ các package cần thiết trong vendor:
  - google/apiclient (Google Drive API)
  - psr/http-message (HTTP message interfaces)
  - guzzlehttp/psr7 (HTTP message implementation)

### 2. Xử lý File

- File được upload tạm thời vào thư mục `uploads/temp/`
- Sau khi upload lên Google Drive thành công, file tạm sẽ được xóa
- Link và metadata của file được lưu trong database
- Sử dụng rate limiting để tránh quá tải server

### 3. Logging

- access/: Log truy cập API
- error/: Log lỗi hệ thống
- upload/: Log quá trình upload file

### 4. Security

- Credentials được lưu trong config/ và không được commit lên git
- Sử dụng .env cho các biến môi trường
- Kiểm tra mime type của file trước khi upload
- Giới hạn kích thước file (mặc định: 10MB)

### 5. Performance

- Sử dụng compression cho response
- Cache các file tĩnh
- Rate limiting cho API endpoints
- Queue system cho upload file lớn

## Hướng dẫn phát triển

1. Clone repository
2. Copy `.env.example` thành `.env` và cấu hình
3. Cài đặt dependencies:
   ```bash
   composer install
   npm install
   ```
4. Tạo các thư mục cần thiết:
   ```bash
   mkdir -p temp uploads/temp logs/{upload,error,access}
   ```
5. Cấu hình Google Drive API:
   - Tạo project trong Google Cloud Console
   - Enable Google Drive API
   - Tạo credentials và lưu vào `config/credentials.json`
   - Cấu hình folder ID trong `config/drive.php`

## Maintenance

- Chạy cleanup script định kỳ để xóa file tạm
- Kiểm tra và rotate log files
- Monitor disk usage của thư mục temp và uploads
- Backup database và credentials định kỳ 

# Quản lý nhân sự version 1

## Cài đặt
1. Clone repository
2. Cài đặt dependencies:
   ```bash
   composer install
   ```
3. Copy file `.env.example` thành `.env` và cấu hình
4. Chạy migrations:
   ```bash
   php src/database/migrations/run.php
   ```

## Cấu trúc thư mục
- `src/app/`: Mã nguồn chính
- `src/api/`: API endpoints
- `src/config/`: Cấu hình
- `src/database/`: Database migrations
- `src/routes/`: Route definitions
- `public/`: Public assets

## Lưu ý
- Đảm bảo PHP >= 7.4
- Cần cài đặt extension PDO và JSON
- Cấu hình database trong file `.env`

