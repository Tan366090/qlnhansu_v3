<?php

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

class AllTests extends TestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('All Tests');
        
        // Thêm tất cả các test class vào suite
        $suite->addTestSuite(AuthTest::class);
        $suite->addTestSuite(BenefitTest::class);
        $suite->addTestSuite(TrainingTest::class);
        $suite->addTestSuite(PayrollTest::class);
        $suite->addTestSuite(AttendanceTest::class);
        $suite->addTestSuite(EmployeeTest::class);
        $suite->addTestSuite(ContractTest::class);
        $suite->addTestSuite(PositionTest::class);
        $suite->addTestSuite(DepartmentTest::class);
        $suite->addTestSuite(PerformanceTest::class);
        $suite->addTestSuite(LeaveTest::class);

        return $suite;
    }
} 