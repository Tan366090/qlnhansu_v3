// Core Modules
import { initializeDashboard } from './modules/dashboard.js';
import { initializeRealtime } from './modules/dashboard-realtime.js';
import { initializeMenuSearch } from './modules/menu-search.js';
import { initializeRecentMenu } from './modules/recent-menu.js';
import { initializeGlobalSearch } from './modules/global-search.js';
import { initializeUserProfile } from './modules/user-profile.js';
import { initializeExportData } from './modules/export-data.js';
import { initializeAIAnalysis } from './modules/ai-analysis.js';
import { initializeGamification } from './modules/gamification.js';
import { initializeMobileStats } from './modules/mobile-stats.js';
import { initializeActivityFilter } from './modules/activity-filter.js';
import { initializeNotificationHandler } from './modules/notification-handler.js';
import { initializeLoadingOverlay } from './modules/loading-overlay.js';
// import { initializeDarkMode } from './modules/dark-mode.js';
import { initializeDashboardAdmin } from './modules/dashboard-admin.js';

// Utility Modules
import { utils } from './modules/utils.js';
import { authUtils } from './modules/auth_utils.js';
import { apiUtils } from './modules/api-utils.js';
import { errorLogger } from './modules/error-logger.js';

// HR Management Modules
import { initializeEmployees } from './modules/employees.js';
import { initializeDepartments } from './modules/departments.js';
import { initializePositions } from './modules/positions.js';
import { initializeAttendance } from './modules/attendance-check.js';
import { initializeLeave } from './modules/leave-list.js';
import { initializeTraining } from './modules/training.js';
import { initializeKPI } from './modules/kpi.js';
import { initializeEquipment } from './modules/equipment.js';
import { initializeDocuments } from './modules/documents.js';
import { initializeCertificates } from './modules/certificates.js';

// Additional Modules
import { initializeSalary } from './modules/salary.js';
import { initializeSalaryConfig } from './modules/salary-config.js';
import { initializeSalaryReports } from './modules/salary-reports.js';
import { initializePerformance } from './modules/performance.js';
import { initializeSettings } from './modules/settings.js';
import { initializeProjects } from './modules/projects.js';
import { initializeBenefits } from './modules/benefits.js';
import { initializeRecruitment } from './modules/recruitment.js';
import { initializeTrainingCourses } from './modules/training-courses.js';
import { initializeLeaveRegister } from './modules/leave-register.js';
import { initializeAttendanceHistory } from './modules/attendance-history.js';
import { initializeEmployeeEdit } from './modules/employee-edit.js';
import { initializeEmployeeForm } from './modules/employee-form.js';
import { initializePositionList } from './modules/position-list.js';
import { initializeEmployeeList } from './modules/employee-list.js';
import { initializeApiService } from './modules/api_service.js';
import { initializeDepartment } from './modules/department.js';
import { initializeMainContent } from './modules/main-content.js';
import { initializeModules } from './modules/modules.js';
import { initializeRoleManager } from './modules/role_manager.js';
import { initializeUser } from './modules/user.js';

// Database Table Modules
import { initializeActivities } from './modules/activities.js';
import { initializeAuditLogs } from './modules/audit_logs.js';
import { initializeBonuses } from './modules/bonuses.js';
import { initializeCandidates } from './modules/candidates.js';
import { initializeContracts } from './modules/contracts.js';
import { initializeDegrees } from './modules/degrees.js';
import { initializeDocumentVersions } from './modules/document_versions.js';
import { initializeFamilyMembers } from './modules/family_members.js';
import { initializeHolidays } from './modules/holidays.js';
import { initializeInsurance } from './modules/insurance.js';
import { initializeInterviews } from './modules/interviews.js';
import { initializeJobPositions } from './modules/job_positions.js';
import { initializeLoginAttempts } from './modules/login_attempts.js';
import { initializeOnboarding } from './modules/onboarding.js';
import { initializeProjectResources } from './modules/project_resources.js';
import { initializeProjectTasks } from './modules/project_tasks.js';
import { initializeRateLimits } from './modules/rate_limits.js';
import { initializeRoles } from './modules/roles.js';
import { initializeRolePermissions } from './modules/role_permissions.js';
import { initializeSessions } from './modules/sessions.js';
import { initializeTasks } from './modules/tasks.js';
import { initializeTrainingEvaluations } from './modules/training_evaluations.js';
import { initializeTrainingRegistrations } from './modules/training_registrations.js';
import { initializeUserProfiles } from './modules/user_profiles.js';
import { initializeWorkSchedules } from './modules/work_schedules.js';

// Export all modules
export {
    // Core Modules
    initializeDashboard,
    initializeRealtime,
    initializeMenuSearch,
    initializeRecentMenu,
    initializeGlobalSearch,
    initializeUserProfile,
    initializeExportData,
    initializeAIAnalysis,
    initializeGamification,
    initializeMobileStats,
    initializeActivityFilter,
    initializeNotificationHandler,
    initializeLoadingOverlay,
    // initializeDarkMode,
    initializeDashboardAdmin,

    // Utility Modules
    utils,
    authUtils,
    apiUtils,
    errorLogger,

    // HR Management Modules
    initializeEmployees,
    initializeDepartments,
    initializePositions,
    initializeAttendance,
    initializeLeave,
    initializeTraining,
    initializeKPI,
    initializeEquipment,
    initializeDocuments,
    initializeCertificates,

    // Additional Modules
    initializeSalary,
    initializeSalaryConfig,
    initializeSalaryReports,
    initializePerformance,
    initializeSettings,
    initializeProjects,
    initializeBenefits,
    initializeRecruitment,
    initializeTrainingCourses,
    initializeLeaveRegister,
    initializeAttendanceHistory,
    initializeEmployeeEdit,
    initializeEmployeeForm,
    initializePositionList,
    initializeEmployeeList,
    initializeApiService,
    initializeDepartment,
    initializeMainContent,
    initializeModules,
    initializeRoleManager,
    initializeUser,

    // Database Table Modules
    initializeActivities,
    initializeAuditLogs,
    initializeBonuses,
    initializeCandidates,
    initializeContracts,
    initializeDegrees,
    initializeDocumentVersions,
    initializeFamilyMembers,
    initializeHolidays,
    initializeInsurance,
    initializeInterviews,
    initializeJobPositions,
    initializeLoginAttempts,
    initializeOnboarding,
    initializeProjectResources,
    initializeProjectTasks,
    initializeRateLimits,
    initializeRoles,
    initializeRolePermissions,
    initializeSessions,
    initializeTasks,
    initializeTrainingEvaluations,
    initializeTrainingRegistrations,
    initializeUserProfiles,
    initializeWorkSchedules
}; 