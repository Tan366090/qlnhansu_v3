import request from "supertest";
import app from "../../src/app.js";
import Department from "../../src/models/Department.js";

describe("Department Controller", () => {
    let testDepartment;
    let authToken;

    beforeAll(async () => {
        // Create test department
        testDepartment = await Department.create({
            name: "Test Department",
            description: "For testing purposes"
        });

        // Login to get token
        const loginResponse = await request(app)
            .post("/api/auth/login")
            .send({
                username: "admin",
                password: "admin123"
            });
        authToken = loginResponse.body.data.token;
    });

    afterAll(async () => {
        // Clean up test data
        await Department.destroy({ where: { id: testDepartment.id } });
    });

    describe("GET /api/departments", () => {
        it("should return all departments", async () => {
            const response = await request(app)
                .get("/api/departments")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(Array.isArray(response.body.data)).toBe(true);
        });
    });

    describe("GET /api/departments/:id", () => {
        it("should return department by id", async () => {
            const response = await request(app)
                .get(`/api/departments/${testDepartment.id}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(response.body.data.id).toBe(testDepartment.id);
        });

        it("should return 404 for non-existent department", async () => {
            const response = await request(app)
                .get("/api/departments/99999")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(404);
            expect(response.body.success).toBe(false);
        });
    });

    describe("POST /api/departments", () => {
        it("should create new department", async () => {
            const newDepartment = {
                name: "New Department",
                description: "New department description"
            };

            const response = await request(app)
                .post("/api/departments")
                .set("Authorization", `Bearer ${authToken}`)
                .send(newDepartment);

            expect(response.status).toBe(201);
            expect(response.body.success).toBe(true);
            expect(response.body.data.name).toBe(newDepartment.name);

            // Clean up
            await Department.destroy({ where: { id: response.body.data.id } });
        });

        it("should return 400 for invalid data", async () => {
            const invalidDepartment = {
                // Missing required name field
                description: "Invalid department"
            };

            const response = await request(app)
                .post("/api/departments")
                .set("Authorization", `Bearer ${authToken}`)
                .send(invalidDepartment);

            expect(response.status).toBe(400);
            expect(response.body.success).toBe(false);
        });
    });

    describe("PUT /api/departments/:id", () => {
        it("should update department", async () => {
            const updateData = {
                name: "Updated Department",
                description: "Updated description"
            };

            const response = await request(app)
                .put(`/api/departments/${testDepartment.id}`)
                .set("Authorization", `Bearer ${authToken}`)
                .send(updateData);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(response.body.data.name).toBe(updateData.name);
        });
    });

    describe("DELETE /api/departments/:id", () => {
        it("should delete department", async () => {
            const response = await request(app)
                .delete(`/api/departments/${testDepartment.id}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);

            // Verify deletion
            const deletedDepartment = await Department.findByPk(testDepartment.id);
            expect(deletedDepartment).toBeNull();
        });
    });
}); 