import winston from "winston";
import { join } from "path";

// Create default logger
const logger = winston.createLogger({
    level: "info",
    format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.json()
    ),
    transports: [
        new winston.transports.File({ 
            filename: join("logs", "error.log"), 
            level: "error" 
        }),
        new winston.transports.File({ 
            filename: join("logs", "combined.log") 
        })
    ]
});

// Add console transport in non-production environment
if (process.env.NODE_ENV !== "production") {
    logger.add(new winston.transports.Console({
        format: winston.format.combine(
            winston.format.colorize(),
            winston.format.simple()
        )
    }));
}

// Function to create a labeled logger
export const createLogger = (label) => {
    return winston.createLogger({
        level: "info",
        format: winston.format.combine(
            winston.format.label({ label }),
            winston.format.timestamp(),
            winston.format.json()
        ),
        transports: [
            new winston.transports.File({ 
                filename: join("logs", "error.log"), 
                level: "error" 
            }),
            new winston.transports.File({ 
                filename: join("logs", "combined.log") 
            })
        ]
    });
};

export default logger; 