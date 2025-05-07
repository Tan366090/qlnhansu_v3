import request from "supertest";
import app from "../../src/app.js";
import { Employee, Department } from "../../src/models/index.js";

describe("Employee Controller", () => {
    let testEmployee;
    let testDepartment;
    let authToken;

    beforeAll(async () => {
        // Create test department
        testDepartment = await Department.create({
            name: "Test Department",
            description: "For testing purposes"
        });

        // Create test employee
        testEmployee = await Employee.create({
            name: "Test Employee",
            email: "test@example.com",
            phone: "1234567890",
            address: "Test Address",
            departmentId: testDepartment.id,
            positionId: "1",
            salary: 50000,
            startDate: new Date(),
            status: "active"
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
        await Employee.destroy({ where: { id: testEmployee.id } });
        await Department.destroy({ where: { id: testDepartment.id } });
    });

    describe("GET /api/employees", () => {
        it("should return all employees", async () => {
            const response = await request(app)
                .get("/api/employees")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(Array.isArray(response.body.data)).toBe(true);
        });
    });

    describe("GET /api/employees/:id", () => {
        it("should return employee by id", async () => {
            const response = await request(app)
                .get(`/api/employees/${testEmployee.id}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(response.body.data.id).toBe(testEmployee.id);
        });

        it("should return 404 for non-existent employee", async () => {
            const response = await request(app)
                .get("/api/employees/99999")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(404);
            expect(response.body.success).toBe(false);
        });
    });

    describe("POST /api/employees", () => {
        it("should create new employee", async () => {
            const newEmployee = {
                name: "New Employee",
                email: "new@example.com",
                phone: "0987654321",
                address: "New Address",
                departmentId: testDepartment.id,
                positionId: "2",
                salary: 45000,
                startDate: new Date(),
                status: "active"
            };

            const response = await request(app)
                .post("/api/employees")
                .set("Authorization", `Bearer ${authToken}`)
                .send(newEmployee);

            expect(response.status).toBe(201);
            expect(response.body.success).toBe(true);
            expect(response.body.data.name).toBe(newEmployee.name);

            // Clean up
            await Employee.destroy({ where: { id: response.body.data.id } });
        });

        it("should return 400 for invalid data", async () => {
            const invalidEmployee = {
                name: "Invalid Employee",
                // Missing required fields
            };

            const response = await request(app)
                .post("/api/employees")
                .set("Authorization", `Bearer ${authToken}`)
                .send(invalidEmployee);

            expect(response.status).toBe(400);
            expect(response.body.success).toBe(false);
        });
    });

    describe("PUT /api/employees/:id", () => {
        it("should update employee", async () => {
            const updatedData = {
                name: "Updated Employee",
                salary: 55000
            };

            const response = await request(app)
                .put(`/api/employees/${testEmployee.id}`)
                .set("Authorization", `Bearer ${authToken}`)
                .send(updatedData);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);
            expect(response.body.data.name).toBe(updatedData.name);
            expect(response.body.data.salary).toBe(updatedData.salary);
        });

        it("should return 404 for non-existent employee", async () => {
            const response = await request(app)
                .put("/api/employees/99999")
                .set("Authorization", `Bearer ${authToken}`)
                .send({ name: "Updated" });

            expect(response.status).toBe(404);
            expect(response.body.success).toBe(false);
        });
    });

    describe("DELETE /api/employees/:id", () => {
        it("should delete employee", async () => {
            const response = await request(app)
                .delete(`/api/employees/${testEmployee.id}`)
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(200);
            expect(response.body.success).toBe(true);

            // Verify employee is deleted
            const deletedEmployee = await Employee.findByPk(testEmployee.id);
            expect(deletedEmployee).toBeNull();
        });

        it("should return 404 for non-existent employee", async () => {
            const response = await request(app)
                .delete("/api/employees/99999")
                .set("Authorization", `Bearer ${authToken}`);

            expect(response.status).toBe(404);
            expect(response.body.success).toBe(false);
        });
    });
}); 