<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class AttendanceController extends BaseController
{
    protected $attendanceModel;
    protected $employeeModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function getTodayAttendance()
    {
        try {
            $today = date('Y-m-d');
            $attendance = $this->attendanceModel->getAttendanceByDate($today);
            
            // Nếu không có dữ liệu chấm công, trả về danh sách nhân viên với trạng thái chưa chấm công
            if (empty($attendance)) {
                $employees = $this->employeeModel->getAllActiveEmployees();
                $result = [];
                foreach ($employees as $employee) {
                    $result[] = [
                        'employee_id' => $employee['id'],
                        'employee_name' => $employee['full_name'],
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'attendance_symbol' => 'A', // A = Absent
                        'work_duration_hours' => 0
                    ];
                }
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => $result
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
?> 