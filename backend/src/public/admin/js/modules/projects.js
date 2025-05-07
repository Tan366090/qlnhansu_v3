// Projects Module
class Projects {
    constructor() {
        this.baseUrl = 'http://localhost/qlnhansu_V2/backend/src/public/api/projects';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadProjects();
        this.loadProjectMembers();
        this.loadTasks();
        this.loadResources();
        this.loadProgress();
        this.loadReports();
    }

    setupEventListeners() {
        // Project management
        document.getElementById('addProjectBtn')?.addEventListener('click', () => this.addProject());
        document.getElementById('editProjectBtn')?.addEventListener('click', () => this.editProject());
        document.getElementById('deleteProjectBtn')?.addEventListener('click', () => this.deleteProject());

        // Task management
        document.getElementById('addTaskBtn')?.addEventListener('click', () => this.addTask());
        document.getElementById('editTaskBtn')?.addEventListener('click', () => this.editTask());
        document.getElementById('deleteTaskBtn')?.addEventListener('click', () => this.deleteTask());

        // Member management
        document.getElementById('addMemberBtn')?.addEventListener('click', () => this.addMember());
        document.getElementById('removeMemberBtn')?.addEventListener('click', () => this.removeMember());

        // Resource management
        document.getElementById('addResourceBtn')?.addEventListener('click', () => this.addResource());
        document.getElementById('editResourceBtn')?.addEventListener('click', () => this.editResource());
        document.getElementById('deleteResourceBtn')?.addEventListener('click', () => this.deleteResource());

        // Progress tracking
        document.getElementById('updateProgressBtn')?.addEventListener('click', () => this.updateProgress());
        document.getElementById('viewProgressBtn')?.addEventListener('click', () => this.viewProgress());

        // Report management
        document.getElementById('generateReportBtn')?.addEventListener('click', () => this.generateReport());
        document.getElementById('exportReportBtn')?.addEventListener('click', () => this.exportReport());
    }

    // Project management
    async loadProjects() {
        try {
            const response = await fetch(`${this.baseUrl}`);
            const data = await response.json();
            this.renderProjects(data);
        } catch (error) {
            console.error('Error loading projects:', error);
            NotificationUtils.show('Lỗi khi tải danh sách dự án', 'error');
        }
    }

    async addProject(projectData) {
        try {
            const response = await fetch(`${this.baseUrl}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(projectData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm dự án thành công', 'success');
                this.loadProjects();
            }
        } catch (error) {
            console.error('Error adding project:', error);
            NotificationUtils.show('Lỗi khi thêm dự án', 'error');
        }
    }

    // Task management
    async loadTasks() {
        try {
            const response = await fetch(`${this.baseUrl}/tasks`);
            const data = await response.json();
            this.renderTasks(data);
        } catch (error) {
            console.error('Error loading tasks:', error);
            NotificationUtils.show('Lỗi khi tải danh sách công việc', 'error');
        }
    }

    async addTask(taskData) {
        try {
            const response = await fetch(`${this.baseUrl}/tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(taskData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm công việc thành công', 'success');
                this.loadTasks();
            }
        } catch (error) {
            console.error('Error adding task:', error);
            NotificationUtils.show('Lỗi khi thêm công việc', 'error');
        }
    }

    // Member management
    async loadProjectMembers() {
        try {
            const response = await fetch(`${this.baseUrl}/members`);
            const data = await response.json();
            this.renderProjectMembers(data);
        } catch (error) {
            console.error('Error loading project members:', error);
            NotificationUtils.show('Lỗi khi tải danh sách thành viên', 'error');
        }
    }

    async addMember(memberData) {
        try {
            const response = await fetch(`${this.baseUrl}/members`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(memberData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm thành viên thành công', 'success');
                this.loadProjectMembers();
            }
        } catch (error) {
            console.error('Error adding member:', error);
            NotificationUtils.show('Lỗi khi thêm thành viên', 'error');
        }
    }

    // Resource management
    async loadResources() {
        try {
            const response = await fetch(`${this.baseUrl}/resources`);
            const data = await response.json();
            this.renderResources(data);
        } catch (error) {
            console.error('Error loading resources:', error);
            NotificationUtils.show('Lỗi khi tải tài nguyên', 'error');
        }
    }

    async addResource(resourceData) {
        try {
            const response = await fetch(`${this.baseUrl}/resources`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(resourceData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm tài nguyên thành công', 'success');
                this.loadResources();
            }
        } catch (error) {
            console.error('Error adding resource:', error);
            NotificationUtils.show('Lỗi khi thêm tài nguyên', 'error');
        }
    }

    // Progress tracking
    async loadProgress() {
        try {
            const response = await fetch(`${this.baseUrl}/progress`);
            const data = await response.json();
            this.renderProgress(data);
        } catch (error) {
            console.error('Error loading progress:', error);
            NotificationUtils.show('Lỗi khi tải tiến độ', 'error');
        }
    }

    async updateProgress(progressData) {
        try {
            const response = await fetch(`${this.baseUrl}/progress`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(progressData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Cập nhật tiến độ thành công', 'success');
                this.loadProgress();

                // Tích hợp với module Hiệu suất
                await this.updateMemberPerformance(progressData);
            }
        } catch (error) {
            console.error('Error updating progress:', error);
            NotificationUtils.show('Lỗi khi cập nhật tiến độ', 'error');
        }
    }

    async updateMemberPerformance(progressData) {
        try {
            const performanceData = {
                employee_id: progressData.employee_id,
                project_id: progressData.project_id,
                task_id: progressData.task_id,
                completion_rate: progressData.completion_rate,
                quality_score: progressData.quality_score,
                deadline_met: progressData.deadline_met,
                comments: progressData.comments
            };

            const response = await fetch('http://localhost/qlnhansu_V2/backend/src/public/api/performance/updates', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(performanceData)
            });

            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Cập nhật hiệu suất thành công', 'success');
            }
        } catch (error) {
            console.error('Error updating member performance:', error);
            NotificationUtils.show('Lỗi khi cập nhật hiệu suất', 'error');
        }
    }

    // Report management
    async loadReports() {
        try {
            const response = await fetch(`${this.baseUrl}/reports`);
            const data = await response.json();
            this.renderReports(data);
        } catch (error) {
            console.error('Error loading reports:', error);
            NotificationUtils.show('Lỗi khi tải báo cáo', 'error');
        }
    }

    async generateReport(reportData) {
        try {
            const response = await fetch(`${this.baseUrl}/reports/generate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(reportData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Tạo báo cáo thành công', 'success');
                this.loadReports();
            }
        } catch (error) {
            console.error('Error generating report:', error);
            NotificationUtils.show('Lỗi khi tạo báo cáo', 'error');
        }
    }

    async exportReport(reportId) {
        try {
            const response = await fetch(`${this.baseUrl}/reports/${reportId}/export`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `project_report_${reportId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            console.error('Error exporting report:', error);
            NotificationUtils.show('Lỗi khi xuất báo cáo', 'error');
        }
    }

    // Rendering methods
    renderProjects(projects) {
        const container = document.getElementById('projectsTable');
        if (!container) return;

        container.innerHTML = projects.map(project => `
            <tr>
                <td>${project.id}</td>
                <td>${project.name}</td>
                <td>${project.description}</td>
                <td>${project.start_date}</td>
                <td>${project.end_date}</td>
                <td>${project.status}</td>
                <td>${project.progress}%</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="projects.editProject(${project.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="projects.deleteProject(${project.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderTasks(tasks) {
        const container = document.getElementById('tasksTable');
        if (!container) return;

        container.innerHTML = tasks.map(task => `
            <tr>
                <td>${task.id}</td>
                <td>${task.project_name}</td>
                <td>${task.title}</td>
                <td>${task.description}</td>
                <td>${task.assignee}</td>
                <td>${task.due_date}</td>
                <td>${task.status}</td>
                <td>${task.priority}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="projects.editTask(${task.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="projects.deleteTask(${task.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderProjectMembers(members) {
        const container = document.getElementById('membersTable');
        if (!container) return;

        container.innerHTML = members.map(member => `
            <tr>
                <td>${member.id}</td>
                <td>${member.project_name}</td>
                <td>${member.employee_name}</td>
                <td>${member.role}</td>
                <td>${member.join_date}</td>
                <td>${member.status}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="projects.removeMember(${member.id})">
                        <i class="fas fa-user-minus"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderResources(resources) {
        const container = document.getElementById('resourcesTable');
        if (!container) return;

        container.innerHTML = resources.map(resource => `
            <tr>
                <td>${resource.id}</td>
                <td>${resource.name}</td>
                <td>${resource.type}</td>
                <td>${resource.quantity}</td>
                <td>${resource.unit}</td>
                <td>${resource.status}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="projects.editResource(${resource.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="projects.deleteResource(${resource.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderProgress(progress) {
        const container = document.getElementById('progressContainer');
        if (!container) return;

        container.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h5>Tiến độ dự án</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tổng tiến độ</label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: ${progress.overall}%">
                                ${progress.overall}%
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tiến độ theo nhiệm vụ</label>
                        ${progress.tasks.map(task => `
                            <div class="task-progress">
                                <span>${task.name}</span>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: ${task.progress}%">
                                        ${task.progress}%
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    renderReports(reports) {
        const container = document.getElementById('reportsTable');
        if (!container) return;

        container.innerHTML = reports.map(report => `
            <tr>
                <td>${report.id}</td>
                <td>${report.title}</td>
                <td>${report.type}</td>
                <td>${report.period}</td>
                <td>${report.status}</td>
                <td>${report.created_at}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="projects.viewReport(${report.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="projects.exportReport(${report.id})">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="projects.deleteReport(${report.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

// Initialize projects module
const projects = new Projects(); 