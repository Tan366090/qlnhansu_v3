import winston from "winston";
import path from "path";
import config from "./config";

const logger = winston.createLogger({
    level: config.logger.level,
    format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.json()
    ),
    transports: [
        new winston.transports.File({ 
            filename: path.join(config.logger.path, "error.log"),
            level: "error"
        }),
        new winston.transports.File({ 
            filename: path.join(config.logger.path, "combined.log")
        })
    ]
});

if (config.app.debug) {
    logger.add(new winston.transports.Console({
        format: winston.format.combine(
            winston.format.colorize(),
            winston.format.simple()
        )
    }));
}

export default logger;
