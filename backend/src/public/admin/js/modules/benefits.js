// Benefits Module
class Benefits {
    constructor() {
        this.baseUrl = 'http://localhost/qlnhansu_V2/backend/src/public/api/benefits';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadInsurance();
        this.loadPolicies();
        this.loadApplications();
        this.loadCosts();
        this.loadReports();
    }

    setupEventListeners() {
        // Insurance management
        document.getElementById('addInsuranceBtn')?.addEventListener('click', () => this.addInsurance());
        document.getElementById('editInsuranceBtn')?.addEventListener('click', () => this.editInsurance());
        document.getElementById('deleteInsuranceBtn')?.addEventListener('click', () => this.deleteInsurance());

        // Policy management
        document.getElementById('addPolicyBtn')?.addEventListener('click', () => this.addPolicy());
        document.getElementById('editPolicyBtn')?.addEventListener('click', () => this.editPolicy());
        document.getElementById('deletePolicyBtn')?.addEventListener('click', () => this.deletePolicy());

        // Application management
        document.getElementById('addApplicationBtn')?.addEventListener('click', () => this.addApplication());
        document.getElementById('approveApplicationBtn')?.addEventListener('click', () => this.approveApplication());
        document.getElementById('rejectApplicationBtn')?.addEventListener('click', () => this.rejectApplication());

        // Cost management
        document.getElementById('calculateCostBtn')?.addEventListener('click', () => this.calculateCost());
        document.getElementById('exportCostBtn')?.addEventListener('click', () => this.exportCost());

        // Report management
        document.getElementById('generateReportBtn')?.addEventListener('click', () => this.generateReport());
        document.getElementById('exportReportBtn')?.addEventListener('click', () => this.exportReport());
    }

    // Insurance management
    async loadInsurance() {
        try {
            const response = await fetch(`${this.baseUrl}/insurance`);
            const data = await response.json();
            this.renderInsurance(data);
        } catch (error) {
            console.error('Error loading insurance:', error);
            NotificationUtils.show('Lỗi khi tải thông tin bảo hiểm', 'error');
        }
    }

    async addInsurance(insuranceData) {
        try {
            const response = await fetch(`${this.baseUrl}/insurance`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(insuranceData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm bảo hiểm thành công', 'success');
                this.loadInsurance();
            }
        } catch (error) {
            console.error('Error adding insurance:', error);
            NotificationUtils.show('Lỗi khi thêm bảo hiểm', 'error');
        }
    }

    // Policy management
    async loadPolicies() {
        try {
            const response = await fetch(`${this.baseUrl}/policies`);
            const data = await response.json();
            this.renderPolicies(data);
        } catch (error) {
            console.error('Error loading policies:', error);
            NotificationUtils.show('Lỗi khi tải chính sách phúc lợi', 'error');
        }
    }

    async addPolicy(policyData) {
        try {
            const response = await fetch(`${this.baseUrl}/policies`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(policyData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm chính sách thành công', 'success');
                this.loadPolicies();
            }
        } catch (error) {
            console.error('Error adding policy:', error);
            NotificationUtils.show('Lỗi khi thêm chính sách', 'error');
        }
    }

    // Application management
    async loadApplications() {
        try {
            const response = await fetch(`${this.baseUrl}/applications`);
            const data = await response.json();
            this.renderApplications(data);
        } catch (error) {
            console.error('Error loading applications:', error);
            NotificationUtils.show('Lỗi khi tải đơn đăng ký', 'error');
        }
    }

    async addApplication(applicationData) {
        try {
            const response = await fetch(`${this.baseUrl}/applications`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(applicationData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Thêm đơn đăng ký thành công', 'success');
                this.loadApplications();
            }
        } catch (error) {
            console.error('Error adding application:', error);
            NotificationUtils.show('Lỗi khi thêm đơn đăng ký', 'error');
        }
    }

    async approveApplication(applicationId) {
        try {
            const response = await fetch(`${this.baseUrl}/applications/${applicationId}/approve`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Duyệt đơn đăng ký thành công', 'success');
                this.loadApplications();
            }
        } catch (error) {
            console.error('Error approving application:', error);
            NotificationUtils.show('Lỗi khi duyệt đơn đăng ký', 'error');
        }
    }

    async rejectApplication(applicationId) {
        try {
            const response = await fetch(`${this.baseUrl}/applications/${applicationId}/reject`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Từ chối đơn đăng ký thành công', 'success');
                this.loadApplications();
            }
        } catch (error) {
            console.error('Error rejecting application:', error);
            NotificationUtils.show('Lỗi khi từ chối đơn đăng ký', 'error');
        }
    }

    // Cost management
    async calculateCost(period) {
        try {
            const response = await fetch(`${this.baseUrl}/costs/calculate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ period })
            });
            const data = await response.json();
            this.renderCosts(data);

            // Tích hợp với module Lương
            await this.updateSalaryDeductions(data);
        } catch (error) {
            console.error('Error calculating costs:', error);
            NotificationUtils.show('Lỗi khi tính toán chi phí', 'error');
        }
    }

    async updateSalaryDeductions(costs) {
        try {
            const deductions = costs.map(cost => ({
                employee_id: cost.employee_id,
                benefit_type: cost.benefit_type,
                amount: cost.amount,
                period: cost.period
            }));

            const response = await fetch('http://localhost/qlnhansu_V2/backend/src/public/api/salary/deductions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deductions })
            });

            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Cập nhật khấu trừ lương thành công', 'success');
            }
        } catch (error) {
            console.error('Error updating salary deductions:', error);
            NotificationUtils.show('Lỗi khi cập nhật khấu trừ lương', 'error');
        }
    }

    async exportCost(period) {
        try {
            const response = await fetch(`${this.baseUrl}/costs/export`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ period })
            });
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `benefits_cost_${period}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            console.error('Error exporting costs:', error);
            NotificationUtils.show('Lỗi khi xuất chi phí', 'error');
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
            a.download = `benefits_report_${reportId}.pdf`;
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
    renderInsurance(insurance) {
        const container = document.getElementById('insuranceTable');
        if (!container) return;

        container.innerHTML = insurance.map(item => `
            <tr>
                <td>${item.id}</td>
                <td>${item.type}</td>
                <td>${item.provider}</td>
                <td>${item.coverage}</td>
                <td>${item.premium}</td>
                <td>${item.status}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="benefits.editInsurance(${item.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="benefits.deleteInsurance(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderPolicies(policies) {
        const container = document.getElementById('policiesTable');
        if (!container) return;

        container.innerHTML = policies.map(policy => `
            <tr>
                <td>${policy.id}</td>
                <td>${policy.name}</td>
                <td>${policy.description}</td>
                <td>${policy.eligibility}</td>
                <td>${policy.benefits}</td>
                <td>${policy.status}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="benefits.editPolicy(${policy.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="benefits.deletePolicy(${policy.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderApplications(applications) {
        const container = document.getElementById('applicationsTable');
        if (!container) return;

        container.innerHTML = applications.map(application => `
            <tr>
                <td>${application.id}</td>
                <td>${application.employee_name}</td>
                <td>${application.benefit_type}</td>
                <td>${application.status}</td>
                <td>${application.applied_date}</td>
                <td>${application.approved_date || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="benefits.approveApplication(${application.id})">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="benefits.rejectApplication(${application.id})">
                        <i class="fas fa-times"></i>
                    </button>
                    <button class="btn btn-sm btn-info" onclick="benefits.viewApplication(${application.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderCosts(costs) {
        const container = document.getElementById('costsTable');
        if (!container) return;

        container.innerHTML = costs.map(cost => `
            <tr>
                <td>${cost.benefit_type}</td>
                <td>${cost.employee_count}</td>
                <td>${cost.total_cost}</td>
                <td>${cost.average_cost}</td>
                <td>${cost.period}</td>
            </tr>
        `).join('');
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
                    <button class="btn btn-sm btn-primary" onclick="benefits.viewReport(${report.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="benefits.exportReport(${report.id})">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="benefits.deleteReport(${report.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

// Initialize benefits module
const benefits = new Benefits(); 