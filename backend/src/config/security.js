// Security configurations
const securityConfig = {
    // Rate limiting
    rateLimit: {
        windowMs: 15 * 60 * 1000, // 15 minutes
        max: 100 // limit each IP to 100 requests per windowMs
    },
    
    // Password requirements
    passwordPolicy: {
        minLength: 8,
        requireUppercase: true,
        requireLowercase: true,
        requireNumbers: true,
        requireSpecialChars: true
    },
    
    // Session security
    sessionConfig: {
        secret: process.env.SESSION_SECRET,
        resave: false,
        saveUninitialized: false,
        cookie: {
            secure: process.env.NODE_ENV === "production",
            httpOnly: true,
            maxAge: 24 * 60 * 60 * 1000 // 24 hours
        }
    },
    
    // CORS configuration
    corsConfig: {
        origin: process.env.ALLOWED_ORIGINS.split(","),
        methods: ["GET", "POST", "PUT", "DELETE"],
        allowedHeaders: ["Content-Type", "Authorization"],
        credentials: true
    },

    // JWT configuration
    jwtConfig: {
        secret: process.env.JWT_SECRET,
        expiresIn: "24h",
        algorithm: "HS256"
    },

    // Input validation
    validationConfig: {
        maxStringLength: 255,
        minStringLength: 1,
        emailRegex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phoneRegex: /^[0-9]{10,11}$/,
        dateRegex: /^\d{4}-\d{2}-\d{2}$/
    },

    // File upload security
    uploadConfig: {
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedMimeTypes: [
            "image/jpeg",
            "image/png",
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        ],
        maxFiles: 5
    }
};

export default securityConfig; 