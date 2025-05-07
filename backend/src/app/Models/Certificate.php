<?php
namespace App\Models;

use PDO;
use PDOException;
use App\Core\Model;

class Certificate extends Model {
    protected $table = 'certificates';
    protected $primaryKey = 'certificate_id';

    protected $fillable = [
        'user_id',
        'title',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'certificate_number',
        'credential_url',
        'file_path',
        'status',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEmployeeCertificates($userId, $status = null)
    {
        $query = $this->where('user_id', $userId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('issue_date', 'DESC')->get();
    }

    public function getDepartmentCertificates($departmentId)
    {
        return $this->whereHas('user', function($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })->get();
    }

    public function getExpiringCertificates($days = 30)
    {
        return $this->where('expiry_date', '>=', date('Y-m-d'))
                   ->where('expiry_date', '<=', date('Y-m-d', strtotime("+$days days")))
                   ->where('status', 'active')
                   ->orderBy('expiry_date', 'ASC')
                   ->get();
    }

    public function getCertificateStatistics()
    {
        return [
            'total_certificates' => $this->count(),
            'active_certificates' => $this->where('status', 'active')->count(),
            'expired_certificates' => $this->where('status', 'expired')->count(),
            'expiring_soon' => $this->where('expiry_date', '>=', date('Y-m-d'))
                                  ->where('expiry_date', '<=', date('Y-m-d', strtotime('+30 days')))
                                  ->where('status', 'active')
                                  ->count()
        ];
    }

    public function updateCertificateStatus()
    {
        $expired = $this->where('expiry_date', '<', date('Y-m-d'))
                       ->where('status', 'active')
                       ->update(['status' => 'expired']);

        return $expired;
    }

    public function getExpiredCertificates()
    {
        return $this->where('expiry_date', '<', date('Y-m-d'))
                   ->where('status', 'active')
                   ->get();
    }

    public function searchCertificates($keyword)
    {
        return $this->where('title', 'LIKE', "%$keyword%")
                   ->orWhere('issuing_organization', 'LIKE', "%$keyword%")
                   ->orWhere('certificate_number', 'LIKE', "%$keyword%")
                   ->get();
    }

    public function createCertificate($data)
    {
        try {
            $certificate = $this->create([
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'issuing_organization' => $data['issuing_organization'],
                'issue_date' => $data['issue_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'certificate_number' => $data['certificate_number'] ?? null,
                'credential_url' => $data['credential_url'] ?? null,
                'file_path' => $data['file_path'] ?? null,
                'status' => 'active'
            ]);

            return [
                'success' => true,
                'certificate' => $certificate
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateCertificate($id, $data)
    {
        try {
            $certificate = $this->find($id);
            if (!$certificate) {
                return [
                    'success' => false,
                    'error' => 'Certificate not found'
                ];
            }

            $certificate->update($data);
            return [
                'success' => true,
                'certificate' => $certificate
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getCertificateDetails($id)
    {
        return $this->with('user')->find($id);
    }

    public function getCertificateStats($departmentId = null)
    {
        $query = $this->select(
            'COUNT(DISTINCT id) as total_certificates',
            'COUNT(DISTINCT user_id) as employees_with_certificates',
            'COUNT(DISTINCT CASE WHEN expiry_date < CURDATE() THEN id END) as expired_certificates',
            'COUNT(DISTINCT CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN id END) as expiring_certificates'
        );

        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->first();
    }
} 