const express = require('express');
const path = require('path');
const cors = require('cors');
const { exec } = require('child_process');

const app = express();
const port = 3000;

// Enable CORS
app.use(cors());

// Serve static files from the correct directory
app.use('/admin', express.static(path.join(__dirname, '..', 'backend', 'src', 'public', 'admin')));
app.use('/public', express.static(path.join(__dirname, '..', 'backend', 'src', 'public')));

// Main route - serve dashboard directly
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'backend', 'src', 'public', 'admin', 'dashboard_admin_V1.php'));
});

// Catch all other routes and serve dashboard
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, '..', 'backend', 'src', 'public', 'admin', 'dashboard_admin_V1.php'));
});

app.listen(port, () => {
  console.log(`Server is running on http://localhost:${port}`);
  console.log(`Admin dashboard is available at http://localhost:${port}`);
  
  // Open Chrome with the dashboard URL
  const url = `http://localhost:${port}`;
  
  // Try different Chrome paths on Windows
  const chromePaths = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    'chrome.exe'
  ];
  
  const openChrome = (index = 0) => {
    if (index >= chromePaths.length) {
      console.error('Could not find Chrome. Please open manually:', url);
      return;
    }
    
    const command = `"${chromePaths[index]}" "${url}"`;
    exec(command, (error) => {
      if (error) {
        console.log(`Trying alternative Chrome path...`);
        openChrome(index + 1);
      } else {
        console.log('Opening Chrome with dashboard...');
      }
    });
  };
  
  openChrome();
}); 