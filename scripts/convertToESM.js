import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function convertToESM(filePath) {
    let content = fs.readFileSync(filePath, "utf8");
    
    // Convert require statements to imports
    content = content.replace(/const\s+(\w+)\s*=\s*require\(['"]([^'"]+)['"]\)/g, "import $1 from \"$2\"");
    
    // Convert module.exports to export default
    content = content.replace(/module\.exports\s*=\s*/, "export default ");
    
    // Convert exports.something to export const something
    content = content.replace(/exports\.(\w+)\s*=\s*/g, "export const $1 = ");
    
    fs.writeFileSync(filePath, content);
    console.log(`Converted ${filePath} to ES modules`);
}

function processDirectory(dir) {
    const files = fs.readdirSync(dir);
    
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory()) {
            // Skip node_modules and other special directories
            if (!["node_modules", ".git", "dist", "build"].includes(file)) {
                processDirectory(fullPath);
            }
        } else if (file.endsWith(".js")) {
            convertToESM(fullPath);
        }
    }
}

// Start conversion from src directory
const srcDir = path.join(__dirname, "../src");
processDirectory(srcDir);
console.log("Conversion completed!"); 