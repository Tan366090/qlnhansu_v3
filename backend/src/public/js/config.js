export const API_ENDPOINTS = {
    BASE_URL: 'http://localhost/qlnhansu_V3/backend/src/public/api',
    AUTH: {
        LOGIN: '/auth/login',
        LOGOUT: '/auth/logout',
        REFRESH: '/auth/refresh'
    },
    EMPLOYEES: {
        LIST: '/employees',
        DETAIL: '/employees/:id',
        CREATE: '/employees',
        UPDATE: '/employees/:id',
        DELETE: '/employees/:id'
    },
    ATTENDANCE: {
        CHECK: '/attendance/check',
        HISTORY: '/attendance/history',
        REPORT: '/attendance/report'
    },
    SALARY: {
        CALCULATE: '/salary/calculate',
        PAYSLIP: '/salary/payslip',
        HISTORY: '/salary/history'
    },
    LEAVE: {
        REQUEST: '/leave/request',
        APPROVE: '/leave/approve',
        HISTORY: '/leave/history'
    },
    AI: {
        HR_TRENDS: '/ai/hr-trends',
        SENTIMENT: '/ai/sentiment'
    },
    GAMIFICATION: {
        LEADERBOARD: '/gamification/leaderboard',
        PROGRESS: '/gamification/progress'
    },
    MOBILE: {
        STATS: '/mobile/stats/usage'
    },
    ACTIVITIES: '/activities',
    NOTIFICATIONS: '/notifications'
}; 