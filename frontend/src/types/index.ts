export interface User {
  id: number;
  username: string;
  email: string;
  role: string;
}

export interface Department {
  id: number;
  name: string;
  description?: string;
}

export interface Employee {
  id: number;
  name: string;
  departmentId: number;
  position: string;
  salary: number;
  joinDate: Date;
} 