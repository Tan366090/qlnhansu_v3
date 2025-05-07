const express = require('express');
const path = require('path');
const cors = require('cors');
const bodyParser = require('body-parser');
const morgan = require('morgan');
const helmet = require('helmet');
const compression = require('compression');
const winston = require('winston');
const chalk = require('chalk');
const figlet = require('figlet');
const mysql = require('mysql2');
const rateLimit = require('express-rate-limit');
const WebSocket = require('ws');
const Redis = require('ioredis');
const jwt = require('jsonwebtoken');

// Cấu hình logger
const logger = winston.createLogger({
    level: 'info',
    format: winston.format.combine(
        winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
        winston.format.printf(({ timestamp, level, message, ...metadata }) => {
            let status = '';
            let color = 'white';
            
            switch(level) {
                case 'error':
                    status = '❌ LỖI';
                    color = 'red';
                    break;
                case 'warn':
                    status = '⚠️ CẢNH BÁO';
                    color = 'yellow';
                    break;
                case 'info':
                    status = 'ℹ️ THÔNG TIN';
                    color = 'cyan';
                    break;
                case 'debug':
                    status = '🔍 DEBUG';
                    color = 'gray';
                    break;
                default:
                    status = '📝 LOG';
                    color = 'white';
            }

            const metadataStr = Object.keys(metadata).length ? JSON.stringify(metadata) : '';
            return chalk[color](
                `┌───────────────────────────────────────────────────────────────┐\n` +
                `│ ${status.padEnd(15)} │ ${timestamp} │\n` +
                `├───────────────────────────────────────────────────────────────┤\n` +
                `│ ${message}\n` +
                (metadataStr ? `│ ${metadataStr}\n` : '') +
                `└───────────────────────────────────────────────────────────────┘\n`
            );
        })
    ),
    transports: [
        new winston.transports.File({ 
            filename: 'logs/error.log', 
            level: 'error',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            )
        }),
        new winston.transports.File({ 
            filename: 'logs/combined.log',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            )
        }),
        new winston.transports.Console()
    ]
});

const app = express();
const port = 3000;

// Cấu hình CORS
app.use(cors({
    origin: ['http://localhost:3000', 'http://localhost:80'],
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(morgan('dev'));
app.use(helmet());
app.use(compression());

// Cấu hình rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100 // limit each IP to 100 requests per windowMs
});

app.use(limiter);

// Cấu hình WebSocket
const wss = new WebSocket.Server({ port: 8080 });

wss.on('connection', (ws) => {
    logger.info('New WebSocket connection established');
    
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            handleWebSocketMessage(ws, data);
        } catch (error) {
            logger.error('WebSocket message error:', error);
        }
    });

    ws.on('close', () => {
        logger.info('WebSocket connection closed');
    });
});

// Xử lý WebSocket message
function handleWebSocketMessage(ws, data) {
    switch (data.type) {
        case 'notification':
            broadcastNotification(data.message);
            break;
        case 'dashboard_update':
            broadcastDashboardUpdate(data.data);
            break;
        case 'chat':
            handleChatMessage(ws, data);
            break;
        case 'equipment_update':
            broadcastEquipmentUpdate(data.data);
            break;
        case 'performance_update':
            broadcastPerformanceUpdate(data.data);
            break;
        case 'recruitment_update':
            broadcastRecruitmentUpdate(data.data);
            break;
        default:
            logger.warn('Unknown WebSocket message type:', data.type);
    }
}

// Broadcast notification
function broadcastNotification(message) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({
                type: 'notification',
                message: message
            }));
        }
    });
}

// Broadcast dashboard update
function broadcastDashboardUpdate(data) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({
                type: 'dashboard_update',
                data: data
            }));
        }
    });
}

// Broadcast equipment update
function broadcastEquipmentUpdate(data) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({
                type: 'equipment_update',
                data: data
            }));
        }
    });
}

// Broadcast performance update
function broadcastPerformanceUpdate(data) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({
                type: 'performance_update',
                data: data
            }));
        }
    });
}

// Broadcast recruitment update
function broadcastRecruitmentUpdate(data) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({
                type: 'recruitment_update',
                data: data
            }));
        }
    });
}

// Xử lý chat message
function handleChatMessage(ws, data) {
    wss.clients.forEach(client => {
        if (client !== ws && client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({
                type: 'chat',
                message: data.message,
                sender: data.sender
            }));
        }
    });
}

// Cấu hình Redis cho caching
const redis = new Redis({
    host: 'localhost',
    port: 6379,
    retryStrategy: (times) => {
        const delay = Math.min(times * 50, 2000);
        return delay;
    }
});

// Middleware caching
const cacheMiddleware = async (req, res, next) => {
    if (req.method !== 'GET') return next();

    const key = `cache:${req.originalUrl}`;
    try {
        const cachedData = await redis.get(key);
        if (cachedData) {
            return res.json(JSON.parse(cachedData));
        }
        next();
    } catch (error) {
        logger.error('Redis error:', error);
        next();
    }
};

app.use(cacheMiddleware);

// Middleware xử lý lỗi
const errorHandler = (err, req, res, next) => {
    logger.error('Error:', err);
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message
    });
};

app.use(errorHandler);

// Middleware xác thực
const authMiddleware = (req, res, next) => {
    const token = req.headers.authorization?.split(' ')[1];
    if (!token) {
        return res.status(401).json({ error: 'Unauthorized' });
    }
    try {
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        req.user = decoded;
        next();
    } catch (error) {
        return res.status(401).json({ error: 'Invalid token' });
    }
};

// Middleware kiểm tra quyền
const checkPermission = (requiredPermission) => {
    return (req, res, next) => {
        if (!req.user.permissions.includes(requiredPermission)) {
            return res.status(403).json({ error: 'Forbidden' });
        }
        next();
    };
};

// Cấu hình routes
const apiRoutes = require('./routes/api');
app.use('/api', apiRoutes);

// Routes
const analysisRoutes = require('./routes/analysis');
const userRoutes = require('./routes/user');

app.use('/api/analysis', analysisRoutes);
app.use('/api/user', userRoutes);

// Cấu hình static files
app.use(express.static(path.join(__dirname, 'public')));

// Cấu hình view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Cấu hình kết nối MySQL
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'qlnhansu'
});

// Kết nối đến MySQL
db.connect((err) => {
    if (err) {
        logger.error('Lỗi kết nối MySQL:', { error: err });
        console.error(chalk.red('Error connecting to MySQL:', err));
        return;
    }
    logger.info('Đã kết nối thành công đến MySQL');
    console.log(chalk.green('Connected to MySQL database'));
});

// API Endpoints
app.get('/api/employees', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT e.*, d.name as department_name, p.name as position_name, u.email, u.full_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON e.user_id = u.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching employees:', error);
        res.status(500).json({ error: 'Error fetching employees' });
    }
});

app.get('/api/departments', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT d.*, COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            GROUP BY d.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching departments:', error);
        res.status(500).json({ error: 'Error fetching departments' });
    }
});

app.get('/api/positions', async (req, res) => {
    try {
        const [rows] = await db.promise().query('SELECT * FROM positions');
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching positions:', error);
        res.status(500).json({ error: 'Error fetching positions' });
    }
});

app.get('/api/performances', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT p.*, e.employee_code, u.full_name
            FROM performances p
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching performances:', error);
        res.status(500).json({ error: 'Error fetching performances' });
    }
});

app.get('/api/payroll', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT p.*, e.employee_code, u.full_name
            FROM payroll p
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching payroll:', error);
        res.status(500).json({ error: 'Error fetching payroll' });
    }
});

app.get('/api/leaves', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT l.*, e.employee_code, u.full_name
            FROM leaves l
            LEFT JOIN employees e ON l.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching leaves:', error);
        res.status(500).json({ error: 'Error fetching leaves' });
    }
});

app.get('/api/trainings', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT t.*, e.employee_code, u.full_name
            FROM trainings t
            LEFT JOIN employees e ON t.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching trainings:', error);
        res.status(500).json({ error: 'Error fetching trainings' });
    }
});

app.get('/api/tasks', async (req, res) => {
    try {
        const [rows] = await db.promise().query(`
            SELECT t.*, e.employee_code, u.full_name
            FROM tasks t
            LEFT JOIN employees e ON t.employee_id = e.id
            LEFT JOIN users u ON e.user_id = u.id
        `);
        res.json(rows);
    } catch (error) {
        logger.error('Error fetching tasks:', error);
        res.status(500).json({ error: 'Error fetching tasks' });
    }
});

// Hiển thị banner
console.log(
    chalk.blue('┌───────────────────────────────────────────────────────────────┐\n') +
    chalk.blue('│ ') + chalk.bold('QLNS V2 - Hệ thống quản lý nhân sự') + chalk.blue(' │\n') +
    chalk.blue('├───────────────────────────────────────────────────────────────┤\n') +
    chalk.blue('│ ') + chalk.green('Server đang chạy tại: ') + chalk.yellow('http://localhost:3000') + chalk.blue(' │\n') +
    chalk.blue('└───────────────────────────────────────────────────────────────┘')
);

// Start the server
app.listen(port, '0.0.0.0', () => {
    logger.info(`Server đang chạy tại port ${port}`);
    console.log(chalk.green(figlet.textSync('QLNS Server', {
        font: 'Standard',
        horizontalLayout: 'default',
        verticalLayout: 'default'
    })));
    console.log('Server started successfully');
    console.log('Static files are served from:', path.join(__dirname, 'public'));
    console.log('Current working directory:', process.cwd());
});

// Graceful shutdown
process.on('SIGTERM', () => {
    logger.info('SIGTERM received. Shutting down gracefully');
    app.close(() => {
        logger.info('Process terminated');
    });
});

process.on('uncaughtException', (err) => {
    logger.error('Lỗi không xử lý được', {
        error: err.message,
        stack: err.stack
    });
    console.error(chalk.red('Uncaught Exception:', err));
    process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
    logger.error('Promise bị từ chối', { reason });
    console.error(chalk.red('Unhandled Rejection:', reason));
}); 