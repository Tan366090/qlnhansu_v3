// Recruitment Module
class Recruitment {
    constructor() {
        this.baseUrl = 'http://localhost/qlnhansu_V2/backend/src/public/api/recruitment';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadPositions();
        this.loadCandidates();
        this.loadInterviews();
        this.loadReports();
    }

    setupEventListeners() {
        // Position management
        document.getElementById('addPositionBtn')?.addEventListener('click', () => this.addPosition());
        document.getElementById('editPositionBtn')?.addEventListener('click', () => this.editPosition());
        document.getElementById('deletePositionBtn')?.addEventListener('click', () => this.deletePosition());

        // Candidate management
        document.getElementById('addCandidateBtn')?.addEventListener('click', () => this.addCandidate());
        document.getElementById('editCandidateBtn')?.addEventListener('click', () => this.editCandidate());
        document.getElementById('deleteCandidateBtn')?.addEventListener('click', () => this.deleteCandidate());
        document.getElementById('searchCandidateBtn')?.addEventListener('click', () => this.searchCandidates());
        document.getElementById('filterCandidateBtn')?.addEventListener('click', () => this.filterCandidates());

        // Interview management
        document.getElementById('scheduleInterviewBtn')?.addEventListener('click', () => this.scheduleInterview());
        document.getElementById('updateInterviewBtn')?.addEventListener('click', () => this.updateInterview());
        document.getElementById('cancelInterviewBtn')?.addEventListener('click', () => this.cancelInterview());

        // Email management
        document.getElementById('sendEmailBtn')?.addEventListener('click', () => this.sendEmail());
        document.getElementById('sendBulkEmailBtn')?.addEventListener('click', () => this.sendBulkEmail());

        // Evaluation management
        document.getElementById('addEvaluationBtn')?.addEventListener('click', () => this.addEvaluation());
        document.getElementById('viewEvaluationBtn')?.addEventListener('click', () => this.viewEvaluation());

        // Report management
        document.getElementById('generateReportBtn')?.addEventListener('click', () => this.generateReport());
        document.getElementById('exportReportBtn')?.addEventListener('click', () => this.exportReport());
    }

    // Position management
    async loadPositions() {
        try {
            const response = await fetch(`${this.baseUrl}/positions`);
            const data = await response.json();
            this.renderPositions(data);
        } catch (error) {
            console.error('Error loading positions:', error);
            NotificationUtils.show('Lỗi khi tải danh sách vị trí', 'error');
        }
    }

    async addPosition(positionData) {
        try {
            const response = await fetch(`${this.baseUrl}/positions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(positionData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm vị trí thành công', 'success');
                this.loadPositions();
            }
        } catch (error) {
            console.error('Error adding position:', error);
            NotificationUtils.show('Lỗi khi thêm vị trí', 'error');
        }
    }

    // Candidate management
    async loadCandidates() {
        try {
            const response = await fetch(`${this.baseUrl}/candidates`);
            const data = await response.json();
            this.renderCandidates(data);
        } catch (error) {
            console.error('Error loading candidates:', error);
            NotificationUtils.show('Lỗi khi tải danh sách ứng viên', 'error');
        }
    }

    async addCandidate(candidateData) {
        try {
            const response = await fetch(`${this.baseUrl}/candidates`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(candidateData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm ứng viên thành công', 'success');
                this.loadCandidates();
                
                // Tích hợp với module Nhân sự
                if (candidateData.status === 'HIRED') {
                    await this.createEmployeeProfile(candidateData);
                }
            }
        } catch (error) {
            console.error('Error adding candidate:', error);
            NotificationUtils.show('Lỗi khi thêm ứng viên', 'error');
        }
    }

    async createEmployeeProfile(candidateData) {
        try {
            const employeeData = {
                name: candidateData.name,
                email: candidateData.email,
                phone: candidateData.phone,
                position: candidateData.position,
                department: candidateData.department,
                join_date: new Date().toISOString().split('T')[0],
                status: 'ACTIVE'
            };

            const response = await fetch('http://localhost/qlnhansu_V2/backend/src/public/api/employees', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(employeeData)
            });

            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Tạo hồ sơ nhân viên thành công', 'success');
            }
        } catch (error) {
            console.error('Error creating employee profile:', error);
            NotificationUtils.show('Lỗi khi tạo hồ sơ nhân viên', 'error');
        }
    }

    // Interview management
    async loadInterviews() {
        try {
            const response = await fetch(`${this.baseUrl}/interviews`);
            const data = await response.json();
            this.renderInterviews(data);
        } catch (error) {
            console.error('Error loading interviews:', error);
            NotificationUtils.show('Lỗi khi tải danh sách phỏng vấn', 'error');
        }
    }

    async scheduleInterview(interviewData) {
        try {
            const response = await fetch(`${this.baseUrl}/interviews`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(interviewData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Lên lịch phỏng vấn thành công', 'success');
                this.loadInterviews();
            }
        } catch (error) {
            console.error('Error scheduling interview:', error);
            NotificationUtils.show('Lỗi khi lên lịch phỏng vấn', 'error');
        }
    }

    // New methods for search and filter
    async searchCandidates(filters) {
        try {
            const response = await fetch(`${this.baseUrl}/candidates/search`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filters)
            });
            const data = await response.json();
            this.renderCandidates(data);
        } catch (error) {
            console.error('Error searching candidates:', error);
            NotificationUtils.show('Lỗi khi tìm kiếm ứng viên', 'error');
        }
    }

    async filterCandidates(filters) {
        try {
            const response = await fetch(`${this.baseUrl}/candidates/filter`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filters)
            });
            const data = await response.json();
            this.renderCandidates(data);
        } catch (error) {
            console.error('Error filtering candidates:', error);
            NotificationUtils.show('Lỗi khi lọc ứng viên', 'error');
        }
    }

    // Email management
    async sendEmail(emailData) {
        try {
            const response = await fetch(`${this.baseUrl}/email/send`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(emailData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Gửi email thành công', 'success');
            }
        } catch (error) {
            console.error('Error sending email:', error);
            NotificationUtils.show('Lỗi khi gửi email', 'error');
        }
    }

    async sendBulkEmail(emailData) {
        try {
            const response = await fetch(`${this.baseUrl}/email/bulk`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(emailData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Gửi email hàng loạt thành công', 'success');
            }
        } catch (error) {
            console.error('Error sending bulk email:', error);
            NotificationUtils.show('Lỗi khi gửi email hàng loạt', 'error');
        }
    }

    // Evaluation management
    async addEvaluation(evaluationData) {
        try {
            const response = await fetch(`${this.baseUrl}/evaluations`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(evaluationData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm đánh giá thành công', 'success');
            }
        } catch (error) {
            console.error('Error adding evaluation:', error);
            NotificationUtils.show('Lỗi khi thêm đánh giá', 'error');
        }
    }

    async viewEvaluation(candidateId) {
        try {
            const response = await fetch(`${this.baseUrl}/evaluations/${candidateId}`);
            const data = await response.json();
            this.renderEvaluation(data);
        } catch (error) {
            console.error('Error viewing evaluation:', error);
            NotificationUtils.show('Lỗi khi xem đánh giá', 'error');
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
            a.download = `report_${reportId}.pdf`;
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
    renderPositions(positions) {
        const container = document.getElementById('positionsTable');
        if (!container) return;

        container.innerHTML = positions.map(position => `
            <tr>
                <td>${position.id}</td>
                <td>${position.title}</td>
                <td>${position.department}</td>
                <td>${position.requirements}</td>
                <td>${position.status}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="recruitment.editPosition(${position.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="recruitment.deletePosition(${position.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderCandidates(candidates) {
        const container = document.getElementById('candidatesTable');
        if (!container) return;

        container.innerHTML = candidates.map(candidate => `
            <tr>
                <td>${candidate.id}</td>
                <td>${candidate.name}</td>
                <td>${candidate.position}</td>
                <td>${candidate.status}</td>
                <td>${candidate.interview_date}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="recruitment.editCandidate(${candidate.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="recruitment.deleteCandidate(${candidate.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderInterviews(interviews) {
        const container = document.getElementById('interviewsTable');
        if (!container) return;

        container.innerHTML = interviews.map(interview => `
            <tr>
                <td>${interview.id}</td>
                <td>${interview.candidate_name}</td>
                <td>${interview.position}</td>
                <td>${interview.date}</td>
                <td>${interview.time}</td>
                <td>${interview.status}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="recruitment.updateInterview(${interview.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="recruitment.cancelInterview(${interview.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderEvaluation(evaluation) {
        const container = document.getElementById('evaluationContainer');
        if (!container) return;

        container.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h5>Đánh giá ứng viên</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Kỹ năng chuyên môn</label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: ${evaluation.technical_skills}%">
                                ${evaluation.technical_skills}%
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kỹ năng mềm</label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: ${evaluation.soft_skills}%">
                                ${evaluation.soft_skills}%
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kinh nghiệm</label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: ${evaluation.experience}%">
                                ${evaluation.experience}%
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nhận xét</label>
                        <textarea class="form-control" rows="3" readonly>${evaluation.comments}</textarea>
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
                    <button class="btn btn-sm btn-primary" onclick="recruitment.viewReport(${report.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="recruitment.exportReport(${report.id})">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="recruitment.deleteReport(${report.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

// Initialize recruitment module
const recruitment = new Recruitment(); 