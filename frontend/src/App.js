import React, { useEffect } from 'react';

const App = () => {
  useEffect(() => {
    // Chuyển hướng đến trang login.html
    window.location.href = '/login.html';
  }, []);

  return null;
};

export default App; 