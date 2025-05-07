export interface Permission {
  id: string;
  name: string;
  description: string;
}

export interface Role {
  id: string;
  name: string;
  permissions: Permission[];
}

export interface UserPermissions {
  userId: number;
  roles: Role[];
  permissions: Permission[];
}

export const HR_PERMISSIONS: Permission[] = [
  { id: 'hr:recruitment:view', name: 'Xem tuyển dụng', description: 'Xem danh sách ứng viên' },
  { id: 'hr:recruitment:create', name: 'Thêm ứng viên', description: 'Thêm ứng viên mới' },
  { id: 'hr:recruitment:update', name: 'Cập nhật ứng viên', description: 'Cập nhật thông tin ứng viên' },
  { id: 'hr:employee:view', name: 'Xem nhân viên', description: 'Xem danh sách nhân viên' },
  { id: 'hr:employee:create', name: 'Thêm nhân viên', description: 'Thêm nhân viên mới' },
  { id: 'hr:employee:update', name: 'Cập nhật nhân viên', description: 'Cập nhật thông tin nhân viên' },
  { id: 'hr:training:view', name: 'Xem đào tạo', description: 'Xem danh sách khóa đào tạo' },
  { id: 'hr:training:create', name: 'Thêm đào tạo', description: 'Thêm khóa đào tạo mới' },
  { id: 'hr:salary:view', name: 'Xem lương', description: 'Xem thông tin lương' },
  { id: 'hr:benefits:view', name: 'Xem phúc lợi', description: 'Xem thông tin phúc lợi' },
  { id: 'hr:benefits:manage', name: 'Quản lý phúc lợi', description: 'Quản lý phúc lợi nhân viên' }
];

export const MANAGER_PERMISSIONS: Permission[] = [
  { id: 'manager:employee:view', name: 'Xem nhân viên', description: 'Xem danh sách nhân viên' },
  { id: 'manager:employee:evaluate', name: 'Đánh giá nhân viên', description: 'Đánh giá hiệu suất nhân viên' },
  { id: 'manager:leave:approve', name: 'Duyệt nghỉ phép', description: 'Duyệt đơn xin nghỉ phép' },
  { id: 'manager:training:view', name: 'Xem đào tạo', description: 'Xem danh sách khóa đào tạo' },
  { id: 'manager:equipment:view', name: 'Xem thiết bị', description: 'Xem danh sách thiết bị' },
  { id: 'manager:team:view', name: 'Xem thống kê nhóm', description: 'Xem thống kê hiệu suất nhóm' },
  { id: 'manager:project:view', name: 'Xem dự án', description: 'Xem thông tin dự án' }
];

export const ADMIN_PERMISSIONS: Permission[] = [
  { id: 'admin:system:manage', name: 'Quản lý hệ thống', description: 'Quản lý cấu hình hệ thống' },
  { id: 'admin:user:manage', name: 'Quản lý người dùng', description: 'Quản lý tài khoản người dùng' },
  { id: 'admin:role:manage', name: 'Quản lý vai trò', description: 'Quản lý phân quyền' },
  { id: 'admin:backup:manage', name: 'Quản lý backup', description: 'Quản lý sao lưu dữ liệu' },
  { id: 'admin:log:view', name: 'Xem log', description: 'Xem nhật ký hệ thống' },
  { id: 'admin:api:manage', name: 'Quản lý API', description: 'Quản lý API keys' },
  { id: 'admin:audit:view', name: 'Xem audit', description: 'Xem nhật ký kiểm tra' }
]; 