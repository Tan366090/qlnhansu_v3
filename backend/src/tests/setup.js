import { jest } from "@jest/globals";
import { config } from "dotenv";
import { fileURLToPath } from "url";
import { dirname, join } from "path";

// Load environment variables from .env.test file
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
config({ path: join(__dirname, "../.env.test") });

// Set test environment variables
process.env.NODE_ENV = "test";
process.env.DB_NAME = "test_db";

// Mock logger
jest.mock("../src/utils/logger", () => ({
    info: jest.fn(),
    error: jest.fn(),
    debug: jest.fn()
}));

// Global test setup
global.beforeAll = async () => {
    // Add any necessary setup code here
    // For example: database connection, creating test data, etc.
};

// Global test teardown
global.afterAll = async () => {
    // Add any necessary cleanup code here
    // For example: closing database connections, cleaning test data, etc.
}; 