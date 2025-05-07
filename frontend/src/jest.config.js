export default {
    testEnvironment: "node",
    testMatch: ["**/tests/**/*.test.js"],
    collectCoverage: true,
    coverageDirectory: "coverage",
    coverageReporters: ["text", "lcov"],
    coveragePathIgnorePatterns: ["/node_modules/", "/tests/"],
    verbose: true,
    setupFiles: ["./tests/setup.js"],
    moduleNameMapper: {
        "^@/(.*)$": "<rootDir>/src/$1"
    },
    transform: {
        "^.+\\.(js|jsx|ts|tsx)$": "babel-jest"
    },
    moduleFileExtensions: ["js", "jsx", "ts", "tsx", "json", "node"],
    testPathIgnorePatterns: ["/node_modules/", "/vendor/"],
    coverageThreshold: {
        global: {
            branches: 50,
            functions: 50,
            lines: 50,
            statements: 50
        }
    },
    extensionsToTreatAsEsm: [".jsx", ".ts", ".tsx"]
}; 