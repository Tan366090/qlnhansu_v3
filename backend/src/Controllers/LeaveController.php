<?php
class LeaveController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getLeaveStatistics() {
        try {
            // Get total leaves
            $totalQuery = "SELECT COUNT(*) as total FROM leaves";
            $totalStmt = $this->db->prepare($totalQuery);
            $totalStmt->execute();
            $totalLeaves = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get approved leaves
            $approvedQuery = "SELECT COUNT(*) as total FROM leaves WHERE status = 'approved'";
            $approvedStmt = $this->db->prepare($approvedQuery);
            $approvedStmt->execute();
            $approvedLeaves = $approvedStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get pending leaves
            $pendingQuery = "SELECT COUNT(*) as total FROM leaves WHERE status = 'pending'";
            $pendingStmt = $this->db->prepare($pendingQuery);
            $pendingStmt->execute();
            $pendingLeaves = $pendingStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get leave type distribution
            $typeQuery = "
                SELECT leave_type, COUNT(*) as count 
                FROM leaves 
                GROUP BY leave_type
            ";
            $typeStmt = $this->db->prepare($typeQuery);
            $typeStmt->execute();
            $leaveTypeDistribution = [];
            while ($row = $typeStmt->fetch(PDO::FETCH_ASSOC)) {
                $leaveTypeDistribution[$row['leave_type']] = (int)$row['count'];
            }

            // Get status distribution
            $statusQuery = "
                SELECT status, COUNT(*) as count 
                FROM leaves 
                GROUP BY status
            ";
            $statusStmt = $this->db->prepare($statusQuery);
            $statusStmt->execute();
            $statusDistribution = [];
            while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
                $statusDistribution[$row['status']] = (int)$row['count'];
            }

            return [
                'total_leaves' => (int)$totalLeaves,
                'approved_leaves' => (int)$approvedLeaves,
                'pending_leaves' => (int)$pendingLeaves,
                'leave_type_distribution' => $leaveTypeDistribution,
                'status_distribution' => $statusDistribution
            ];
        } catch (Exception $e) {
            throw new Exception('Error getting leave statistics: ' . $e->getMessage());
        }
    }
} 