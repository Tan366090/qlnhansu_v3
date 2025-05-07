const fs = require('fs');
const path = require('path');
const axios = require('axios');

// Danh sách các icon cần tải với tên mới từ Font Awesome 6
const icons = [
    { oldName: 'tachometer-alt', newName: 'gauge' },
    { oldName: 'users', newName: 'users' },
    { oldName: 'building', newName: 'building' },
    { oldName: 'briefcase', newName: 'briefcase' },
    { oldName: 'calendar-check', newName: 'calendar-check' },
    { oldName: 'calendar-times', newName: 'calendar-xmark' },
    { oldName: 'graduation-cap', newName: 'graduation-cap' },
    { oldName: 'money-bill-wave', newName: 'money-bill-wave' },
    { oldName: 'chart-line', newName: 'chart-line' },
    { oldName: 'certificate', newName: 'certificate' },
    { oldName: 'file-alt', newName: 'file' },
    { oldName: 'laptop', newName: 'laptop' },
    { oldName: 'plus', newName: 'plus' },
    { oldName: 'search', newName: 'magnifying-glass' },
    { oldName: 'undo', newName: 'rotate-left' },
    { oldName: 'file-excel', newName: 'file-excel' },
    { oldName: 'print', newName: 'print' },
    { oldName: 'exclamation-triangle', newName: 'triangle-exclamation' },
    { oldName: 'chevron-left', newName: 'chevron-left' },
    { oldName: 'chevron-right', newName: 'chevron-right' },
    { oldName: 'eye', newName: 'eye' },
    { oldName: 'key', newName: 'key' },
    { oldName: 'paper-plane', newName: 'paper-plane' },
    { oldName: 'arrow-left', newName: 'arrow-left' },
    { oldName: 'times', newName: 'xmark' },
    { oldName: 'check', newName: 'check' },
    { oldName: 'user', newName: 'user' }
];

// Tạo thư mục output nếu chưa tồn tại
const outputDir = path.join(__dirname, '../frontend/public/assets/icons');
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

// Hàm tải icon từ Font Awesome
async function downloadIcon(icon) {
    try {
        const response = await axios.get(`https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/svgs/solid/${icon.newName}.svg`);
        const svgContent = response.data;
        
        // Lưu SVG vào file với tên cũ
        const svgPath = path.join(outputDir, `${icon.oldName}.svg`);
        fs.writeFileSync(svgPath, svgContent);
        
        console.log(`Đã tải SVG cho icon ${icon.oldName} (${icon.newName})`);
    } catch (error) {
        console.error(`Lỗi khi tải icon ${icon.oldName} (${icon.newName}):`, error.message);
    }
}

// Tạo file index.js để export tất cả các icon
async function createIndexFile() {
    const imports = icons.map(icon => 
        `import ${icon.oldName.replace(/-/g, '_')} from './${icon.oldName}.svg';`
    ).join('\n');

    const exports = `export default {\n${icons.map(icon => 
        `    ${icon.oldName.replace(/-/g, '_')}`
    ).join(',\n')}\n};`;

    const indexContent = `${imports}\n\n${exports}`;
    fs.writeFileSync(path.join(outputDir, 'index.js'), indexContent);
    console.log('Đã tạo file index.js');
}

// Thực hiện tải icon
async function main() {
    console.log('Bắt đầu tải icon...');
    
    for (const icon of icons) {
        await downloadIcon(icon);
    }
    
    await createIndexFile();
    console.log('Hoàn thành!');
}

main(); 