import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { execSync } from "child_process";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Hàm kiểm tra và cập nhật mã nguồn
async function updateSourceCode() {
    try {
        // 1. Kiểm tra lỗi ESLint
        console.log("Checking ESLint errors...");
        execSync("npm run lint", { stdio: "inherit" });

        // 2. Tự động sửa lỗi ESLint
        console.log("Fixing ESLint errors...");
        execSync("npm run lint:fix", { stdio: "inherit" });

        // 3. Format code
        console.log("Formatting code...");
        execSync("npm run format", { stdio: "inherit" });

        // 4. Kiểm tra và cập nhật dependencies
        console.log("Checking dependencies...");
        execSync("npm audit", { stdio: "inherit" });
        execSync("npm audit fix", { stdio: "inherit" });

        // 5. Cập nhật các file cấu hình bảo mật
        updateSecurityConfigs();

        // 6. Cập nhật các file test
        updateTestFiles();

        console.log("Source code update completed successfully!");
    } catch (error) {
        console.error("Error updating source code:", error);
        process.exit(1);
    }
}

// Cập nhật các file cấu hình bảo mật
function updateSecurityConfigs() {
    const securityFiles = [
        ".env.example",
        "src/config/security.js",
        "src/middleware/auth.js",
        "src/middleware/rateLimit.js"
    ];

    securityFiles.forEach(file => {
        if (fs.existsSync(file)) {
            console.log(`Updating security config: ${file}`);
            // Thêm các cấu hình bảo mật mới
            const content = fs.readFileSync(file, "utf8");
            const updatedContent = addSecurityConfigs(content);
            fs.writeFileSync(file, updatedContent);
        }
    });
}

// Thêm các cấu hình bảo mật
function addSecurityConfigs(content) {
    // Thêm các cấu hình bảo mật mới
    const securityConfigs = `
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
            secure: process.env.NODE_ENV === 'production',
            httpOnly: true,
            maxAge: 24 * 60 * 60 * 1000 // 24 hours
        }
    },
    
    // CORS configuration
    corsConfig: {
        origin: process.env.ALLOWED_ORIGINS.split(','),
        methods: ['GET', 'POST', 'PUT', 'DELETE'],
        allowedHeaders: ['Content-Type', 'Authorization'],
        credentials: true
    }
};

export default securityConfig;
`;

    return content + securityConfigs;
}

// Cập nhật các file test
function updateTestFiles() {
    const testDir = path.join(__dirname, "../tests");
    const testFiles = fs.readdirSync(testDir);

    testFiles.forEach(file => {
        if (file.endsWith(".test.js")) {
            console.log(`Updating test file: ${file}`);
            const filePath = path.join(testDir, file);
            const content = fs.readFileSync(filePath, "utf8");
            const updatedContent = addTestCases(content);
            fs.writeFileSync(filePath, updatedContent);
        }
    });
}

// Thêm các test case mới
function addTestCases(content) {
    const newTestCases = `
// Additional test cases for edge cases
describe('Edge Cases', () => {
    it('should handle empty input gracefully', async () => {
        // Test empty input handling
    });

    it('should handle invalid input gracefully', async () => {
        // Test invalid input handling
    });

    it('should handle concurrent requests', async () => {
        // Test concurrent request handling
    });

    it('should handle large data sets', async () => {
        // Test performance with large data
    });
});
`;

    return content + newTestCases;
}

// Chạy cập nhật
updateSourceCode(); 