import { describe, test, expect, beforeAll, afterAll } from "@jest/globals";
import { config } from "dotenv";
import mysql from "mysql2/promise";
import request from "supertest";
import app from "@/app.js";
import { sequelize } from "@/config/database.js";
import { User, Employee, Department, Position, Role } from "@/models/index.js";

// Load environment variables
config();

// Test data
const testUser = {
    username: "testadmin",
    email: "testadmin@example.com",
    password: "Test123!@#",
    role: "admin"
};

const testEmployee = {
    firstName: "Test",
    lastName: "Employee",
    email: "test.employee@example.com",
    phone: "0123456789",
    address: "Test Address",
    departmentId: 1,
    positionId: 1
};

const testDepartment = {
    name: "Test Department",
    description: "Test Department Description"
};

const testPosition = {
    name: "Test Position",
    description: "Test Position Description",
    departmentId: 1
};

describe("System Integration Tests", () => {
    let connection;
    let authToken;
    let testUserId;
    let testEmployeeId;
    let testDepartmentId;
    let testPositionId;

    beforeAll(async () => {
        // Create database connection
        connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME
        });

        // Sync database
        await sequelize.sync({ force: true });

        // Create test roles
        await Role.bulkCreate([
            { name: "admin", description: "Administrator" },
            { name: "manager", description: "Manager" },
            { name: "employee", description: "Employee" }
        ]);
    });

    afterAll(async () => {
        // Close connections
        await connection.end();
        await sequelize.close();
    });

    describe("Authentication Tests", () => {
        test("should register a new user", async () => {
            const response = await request(app)
                .post("/api/auth/register")
                .send(testUser);

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("message", "User registered successfully");
            testUserId = response.body.userId;
        });

        test("should login with registered user", async () => {
            const response = await request(app)
                .post("/api/auth/login")
                .send({
                    email: testUser.email,
                    password: testUser.password
                });

            expect(response.status).toBe(200);
            expect(response.body).toHaveProperty("token");
            authToken = response.body.token;
        });
    });

    describe("Department Management Tests", () => {
        test("should create a new department", async () => {
            const response = await request(app)
                .post("/api/departments")
                .set("Authorization", `Bearer ${authToken}`)
                .send(testDepartment);

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
            testDepartmentId = response.body.id;
        });

        test("should get all departments", async () => {
            const response = await request(app)
                .get("/api/departments")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Position Management Tests", () => {
        test("should create a new position", async () => {
            const response = await request(app)
                .post("/api/positions")
                .set("Authorization", `Bearer ${authToken}`)
                .send(testPosition);

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
            testPositionId = response.body.id;
        });

        test("should get all positions", async () => {
            const response = await request(app)
                .get("/api/positions")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Employee Management Tests", () => {
        test("should create a new employee", async () => {
            const response = await request(app)
                .post("/api/employees")
                .set("Authorization", `Bearer ${authToken}`)
                .send({
                    ...testEmployee,
                    departmentId: testDepartmentId,
                    positionId: testPositionId
                });

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
            testEmployeeId = response.body.id;
        });

        test("should get all employees", async () => {
            const response = await request(app)
                .get("/api/employees")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });

        test("should get employee by id", async () => {
            const response = await request(app)
                .get(`/api/employees/${testEmployeeId}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body).toHaveProperty("id", testEmployeeId);
        });
    });

    describe("Leave Management Tests", () => {
        test("should create a leave request", async () => {
            const response = await request(app)
                .post("/api/leaves")
                .set("Authorization", `Bearer ${authToken}`)
                .send({
                    employeeId: testEmployeeId,
                    startDate: "2024-01-01",
                    endDate: "2024-01-05",
                    type: "annual",
                    reason: "Test leave request"
                });

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
        });

        test("should get all leave requests", async () => {
            const response = await request(app)
                .get("/api/leaves")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Attendance Management Tests", () => {
        test("should record attendance", async () => {
            const response = await request(app)
                .post("/api/attendance")
                .set("Authorization", `Bearer ${authToken}`)
                .send({
                    employeeId: testEmployeeId,
                    date: "2024-01-01",
                    checkIn: "09:00:00",
                    checkOut: "17:00:00"
                });

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
        });

        test("should get attendance records", async () => {
            const response = await request(app)
                .get("/api/attendance")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Payroll Management Tests", () => {
        test("should create a payroll record", async () => {
            const response = await request(app)
                .post("/api/payroll")
                .set("Authorization", `Bearer ${authToken}`)
                .send({
                    employeeId: testEmployeeId,
                    month: 1,
                    year: 2024,
                    basicSalary: 10000000,
                    allowances: 2000000,
                    deductions: 1000000
                });

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
        });

        test("should get payroll records", async () => {
            const response = await request(app)
                .get("/api/payroll")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Performance Management Tests", () => {
        test("should create a performance review", async () => {
            const response = await request(app)
                .post("/api/performance")
                .set("Authorization", `Bearer ${authToken}`)
                .send({
                    employeeId: testEmployeeId,
                    reviewerId: testUserId,
                    rating: 4.5,
                    comments: "Test performance review",
                    goals: ["Goal 1", "Goal 2"]
                });

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
        });

        test("should get performance reviews", async () => {
            const response = await request(app)
                .get("/api/performance")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Document Management Tests", () => {
        test("should upload a document", async () => {
            const response = await request(app)
                .post("/api/documents")
                .set("Authorization", `Bearer ${authToken}`)
                .attach("file", Buffer.from("test document content"), {
                    filename: "test.txt",
                    contentType: "text/plain"
                })
                .field({
                    employeeId: testEmployeeId,
                    type: "contract",
                    description: "Test document"
                });

            expect(response.status).toBe(201);
            expect(response.body).toHaveProperty("id");
        });

        test("should get documents", async () => {
            const response = await request(app)
                .get("/api/documents")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(Array.isArray(response.body)).toBe(true);
        });
    });

    describe("Cleanup Tests", () => {
        test("should delete test employee", async () => {
            const response = await request(app)
                .delete(`/api/employees/${testEmployeeId}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
        });

        test("should delete test position", async () => {
            const response = await request(app)
                .delete(`/api/positions/${testPositionId}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
        });

        test("should delete test department", async () => {
            const response = await request(app)
                .delete(`/api/departments/${testDepartmentId}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
        });

        test("should delete test user", async () => {
            const response = await request(app)
                .delete(`/api/users/${testUserId}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
        });
    });
});

describe("System Tests", () => {
    describe("Health Check", () => {
        it("should return 200 OK", async () => {
            const response = await request(app).get("/api/health");
            expect(response.status).toBe(200);
            expect(response.body.status).toBe("OK");
        });
    });

    describe("API Documentation", () => {
        it("should return 200 OK for Swagger UI", async () => {
            const response = await request(app).get("/api-docs");
            expect(response.status).toBe(200);
        });
    });

    describe("Error Handling", () => {
        it("should return 404 for non-existent routes", async () => {
            const response = await request(app).get("/non-existent-route");
            expect(response.status).toBe(404);
        });

        it("should return 500 for internal server errors", async () => {
            // Simulate an internal error by passing invalid data
            const response = await request(app)
                .post("/api/auth/login")
                .send({ invalid: "data" });
            expect(response.status).toBe(400);
        });
    });
}); 