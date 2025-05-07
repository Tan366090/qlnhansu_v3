import request from "supertest";
import app from "../../src/app.js";
import { hashPassword } from "../../src/utils/hashUtils.js";

describe("Security Tests", () => {
    describe("Authentication", () => {
        it("should prevent brute force attacks", async () => {
            const attempts = 5;
            const responses = [];

            for (let i = 0; i < attempts; i++) {
                const response = await request(app)
                    .post("/api/auth/login")
                    .send({
                        username: "admin",
                        password: "wrongpassword"
                    });
                responses.push(response.status);
            }

            // After multiple failed attempts, should get rate limited
            expect(responses[attempts - 1]).toBe(429);
        });

        it("should require strong passwords", async () => {
            const weakPassword = "123456";
            const strongPassword = "ComplexP@ssw0rd123";

            const weakResponse = await request(app)
                .post("/api/auth/register")
                .send({
                    username: "testuser",
                    password: weakPassword
                });

            const strongResponse = await request(app)
                .post("/api/auth/register")
                .send({
                    username: "testuser2",
                    password: strongPassword
                });

            expect(weakResponse.status).toBe(400);
            expect(strongResponse.status).toBe(201);
        });
    });

    describe("Authorization", () => {
        let userToken;
        let adminToken;

        beforeAll(async () => {
            // Login as regular user
            const userResponse = await request(app)
                .post("/api/auth/login")
                .send({
                    username: "user",
                    password: "user123"
                });
            userToken = userResponse.body.data.token;

            // Login as admin
            const adminResponse = await request(app)
                .post("/api/auth/login")
                .send({
                    username: "admin",
                    password: "admin123"
                });
            adminToken = adminResponse.body.data.token;
        });

        it("should prevent unauthorized access to admin routes", async () => {
            const response = await request(app)
                .get("/api/admin/users")
                .set("Authorization", `Bearer ${userToken}`);

            expect(response.status).toBe(403);
        });

        it("should allow admin access to admin routes", async () => {
            const response = await request(app)
                .get("/api/admin/users")
                .set("Authorization", `Bearer ${adminToken}`);

            expect(response.status).toBe(200);
        });
    });

    describe("Input Validation", () => {
        it("should prevent SQL injection", async () => {
            const maliciousInput = "' OR '1'='1";
            
            const response = await request(app)
                .get(`/api/employees?search=${maliciousInput}`)
                .set("Authorization", `Bearer ${adminToken}`);

            // Should not return all records
            expect(response.body.data.length).toBeLessThan(100);
        });

        it("should prevent XSS attacks", async () => {
            const xssPayload = "<script>alert(\"xss\")</script>";
            
            const response = await request(app)
                .post("/api/employees")
                .set("Authorization", `Bearer ${adminToken}`)
                .send({
                    name: xssPayload,
                    email: "test@example.com"
                });

            // Should sanitize input
            expect(response.body.data.name).not.toContain("<script>");
        });
    });

    describe("Data Protection", () => {
        it("should encrypt sensitive data", async () => {
            const password = "test123";
            const hashedPassword = await hashPassword(password);

            expect(hashedPassword).not.toBe(password);
            expect(hashedPassword).toMatch(/^\$2[aby]\$\d+\$/);
        });

        it("should not expose sensitive information in responses", async () => {
            const response = await request(app)
                .get("/api/users/1")
                .set("Authorization", `Bearer ${adminToken}`);

            expect(response.body.data).not.toHaveProperty("password");
            expect(response.body.data).not.toHaveProperty("salt");
        });
    });
}); 