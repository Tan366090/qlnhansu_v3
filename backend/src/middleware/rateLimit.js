import rateLimit from "express-rate-limit";
import RedisStore from "rate-limit-redis";
import redis from "../utils/redis.js";
import securityConfig from "../config/security.js";
import logger from "../utils/logger.js";

// Tạo rate limiter cho API endpoints
export const apiLimiter = rateLimit({
    store: new RedisStore({
        client: redis,
        prefix: "ratelimit:api:"
    }),
    windowMs: securityConfig.rateLimit.windowMs,
    max: securityConfig.rateLimit.max,
    message: {
        success: false,
        message: "Too many requests, please try again later"
    },
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req, res) => {
        logger.warn(`Rate limit exceeded for IP: ${req.ip}`);
        res.status(429).json({
            success: false,
            message: "Too many requests, please try again later"
        });
    }
});

// Tạo rate limiter cho đăng nhập
export const loginLimiter = rateLimit({
    store: new RedisStore({
        client: redis,
        prefix: "ratelimit:login:"
    }),
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 5, // 5 attempts
    message: {
        success: false,
        message: "Too many login attempts, please try again later"
    },
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req, res) => {
        logger.warn(`Login rate limit exceeded for IP: ${req.ip}`);
        res.status(429).json({
            success: false,
            message: "Too many login attempts, please try again later"
        });
    }
});

// Tạo rate limiter cho đăng ký
export const registerLimiter = rateLimit({
    store: new RedisStore({
        client: redis,
        prefix: "ratelimit:register:"
    }),
    windowMs: 60 * 60 * 1000, // 1 hour
    max: 3, // 3 attempts
    message: {
        success: false,
        message: "Too many registration attempts, please try again later"
    },
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req, res) => {
        logger.warn(`Registration rate limit exceeded for IP: ${req.ip}`);
        res.status(429).json({
            success: false,
            message: "Too many registration attempts, please try again later"
        });
    }
});

// Tạo rate limiter cho reset password
export const resetPasswordLimiter = rateLimit({
    store: new RedisStore({
        client: redis,
        prefix: "ratelimit:reset:"
    }),
    windowMs: 60 * 60 * 1000, // 1 hour
    max: 3, // 3 attempts
    message: {
        success: false,
        message: "Too many password reset attempts, please try again later"
    },
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req, res) => {
        logger.warn(`Password reset rate limit exceeded for IP: ${req.ip}`);
        res.status(429).json({
            success: false,
            message: "Too many password reset attempts, please try again later"
        });
    }
});

// Tạo rate limiter cho upload file
export const uploadLimiter = rateLimit({
    store: new RedisStore({
        client: redis,
        prefix: "ratelimit:upload:"
    }),
    windowMs: 60 * 60 * 1000, // 1 hour
    max: 10, // 10 files
    message: {
        success: false,
        message: "Too many file uploads, please try again later"
    },
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req, res) => {
        logger.warn(`File upload rate limit exceeded for IP: ${req.ip}`);
        res.status(429).json({
            success: false,
            message: "Too many file uploads, please try again later"
        });
    }
}); 