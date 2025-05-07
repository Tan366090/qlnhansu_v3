import request from "supertest";
import app from "../../src/app.js";
import User from "../../src/models/User.js";
import { hashPassword } from "../../src/utils/hashUtils.js";

describe("Login Controller", () => {
    let testUser;

    beforeAll(async () => {
        // Create test user
        const hashedPassword = await hashPassword("test123");
        testUser = await User.create({
            username: "testuser",
            password: hashedPassword,
            role: "user"
        });
    });

    afterAll(async () => {
        // Clean up test data
        await User.destroy({ where: { username: "testuser" } });
    });

    describe("POST /api/auth/login", () => {
        it("should return 400 if username or password is missing", async () => {
            const response = await request(app)
                .post("/api/auth/login")
                .send({});

            expect(response.status).toBe(400);
            expect(response.body.success).toBe(false);
            expect(response.body.message).toBe("Username and password are required");
        });

        it("should return 401 for invalid credentials", async () => {
            const response = await request(app)
                .post("/api/auth/login")
                .send({
                    username: "testuser",
                    password: "wrongpassword"
                });

            expect(response.status).toBe(401);
            expect(response.body.success).toBe(false);
            expect(response.body.message).toBe("Invalid credentials");
        });

        it("should return 200 and token for valid credentials", async () => {
            const response = await request(app)
                .post("/api/auth/login")
                .send({
                    username: "testuser",
                    password: "test123"
                });

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(response.body.message).toBe("Login successful");
            expect(response.body.data.token).toBeDefined();
            expect(response.body.data.user).toBeDefined();
            expect(response.body.data.user.username).toBe("testuser");
        });
    });
}); 