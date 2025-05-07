/**
 * @module user-profile
 * @description Handles user profile functionality
 */

class UserProfile {
    constructor() {
        this.profile = null;
        this.init();
    }

    async init() {
        await this.loadUserProfile();
        this.setupEventListeners();
    }

    async loadUserProfile() {
        try {
            const response = await fetch('/qlnhansu_V2/backend/src/api/routes/user-profile.php');
            if (!response.ok) {
                throw new Error('Failed to load user profile');
            }
            
            this.profile = await response.json();
            this.updateProfileUI();
        } catch (error) {
            console.error('Error loading user profile:', error);
            // Sử dụng dữ liệu mặc định nếu không tải được từ API
            this.profile = {
                profile_id: 1,
                user_id: 1,
                full_name: 'Admin User',
                avatar_url: null,
                date_of_birth: '1990-01-01',
                permanent_address: '123 Main St, Hanoi',
                current_workplace: null,
                gender: 'Male',
                phone_number: '0123456789',
                emergency_contact: 'Jane Doe',
                bank_account: '1234567890',
                tax_code: '123456789',
                nationality: 'Vietnamese',
                ethnicity: 'Kinh',
                religion: 'None',
                marital_status: 'Single',
                id_card_number: '123456789',
                id_card_issue_date: '2010-01-01',
                id_card_issue_place: 'Hanoi'
            };
            this.updateProfileUI();
        }
    }

    updateProfileUI() {
        if (!this.profile) return;

        // Cập nhật avatar
        const avatar = document.querySelector('.user-avatar');
        if (avatar) {
            avatar.src = this.profile.avatar_url || 'male.png';
            avatar.alt = this.profile.full_name;
        }

        // Cập nhật thông tin người dùng
        const userInfo = document.querySelector('.user-info');
        if (userInfo) {
            userInfo.innerHTML = `
                <h4>${this.profile.full_name}</h4>
                <p>${this.profile.gender} | ${this.profile.nationality}</p>
                <p>${this.profile.phone_number}</p>
                <p>${this.profile.permanent_address}</p>
            `;
        }

        // Cập nhật dropdown menu
        const dropdownMenu = document.querySelector('.dropdown-menu');
        if (dropdownMenu) {
            dropdownMenu.innerHTML = `
                <li><a class="dropdown-item" href="#">${this.profile.full_name}</a></li>
                <li><a class="dropdown-item" href="#">${this.profile.phone_number}</a></li>
                <li><a class="dropdown-item" href="#" id="logoutBtn">Đăng xuất</a></li>
            `;
        }
    }

    setupEventListeners() {
        // Thêm event listeners cho các nút chỉnh sửa profile
        const editButtons = document.querySelectorAll('.edit-profile-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', () => this.showEditProfileModal());
        });

        // Thêm event listener cho nút đăng xuất
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.handleLogout());
        }
    }

    showEditProfileModal() {
        // Hiển thị modal chỉnh sửa profile
        const modal = document.getElementById('editProfileModal');
        if (modal) {
            // Điền dữ liệu hiện tại vào form
            const form = modal.querySelector('form');
            if (form) {
                form.elements['full_name'].value = this.profile.full_name;
                form.elements['date_of_birth'].value = this.profile.date_of_birth;
                form.elements['permanent_address'].value = this.profile.permanent_address;
                form.elements['current_workplace'].value = this.profile.current_workplace;
                form.elements['gender'].value = this.profile.gender;
                form.elements['phone_number'].value = this.profile.phone_number;
                form.elements['emergency_contact'].value = this.profile.emergency_contact;
                form.elements['bank_account'].value = this.profile.bank_account;
                form.elements['tax_code'].value = this.profile.tax_code;
                form.elements['nationality'].value = this.profile.nationality;
                form.elements['ethnicity'].value = this.profile.ethnicity;
                form.elements['religion'].value = this.profile.religion;
                form.elements['marital_status'].value = this.profile.marital_status;
                form.elements['id_card_number'].value = this.profile.id_card_number;
                form.elements['id_card_issue_date'].value = this.profile.id_card_issue_date;
                form.elements['id_card_issue_place'].value = this.profile.id_card_issue_place;
            }
            
            // Hiển thị modal
            modal.classList.add('show');
            modal.style.display = 'block';
        }
    }

    async handleLogout() {
        try {
            // Gọi API đăng xuất
            const response = await fetch('/qlnhansu_V2/backend/src/api/routes/auth/logout.php', {
                method: 'POST'
            });
            
            if (response.ok) {
                // Chuyển hướng về trang đăng nhập
                window.location.href = '/qlnhansu_V2/backend/src/public/admin/login.html';
            }
        } catch (error) {
            console.error('Error during logout:', error);
        }
    }
}

// Khởi tạo user profile khi DOM đã tải xong
document.addEventListener('DOMContentLoaded', () => {
    new UserProfile();
}); 