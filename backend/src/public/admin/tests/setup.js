require('@testing-library/jest-dom');

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: jest.fn().mockImplementation(query => ({
        matches: false,
        media: query,
        onchange: null,
        addListener: jest.fn(),
        removeListener: jest.fn(),
        addEventListener: jest.fn(),
        removeEventListener: jest.fn(),
        dispatchEvent: jest.fn(),
    })),
});

// Mock window.getComputedStyle
Object.defineProperty(window, 'getComputedStyle', {
    value: (element) => ({
        position: 'fixed',
        width: '280px',
        zIndex: '1040',
        display: 'block'
    })
}); 