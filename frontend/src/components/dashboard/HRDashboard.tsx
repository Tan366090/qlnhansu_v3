import React from 'react';
import { Card, Row, Col, Statistic, Table, Button } from 'antd';
import type { ColumnsType } from 'antd/es/table';
import type { ColProps } from 'antd/es/col';
import { 
  UserAddOutlined, 
  FileTextOutlined, 
  ReadOutlined, 
  DollarOutlined,
  TeamOutlined,
  ClockCircleOutlined,
  ToolOutlined,
  BarChartOutlined
} from '@ant-design/icons';
import { HR_PERMISSIONS } from '../../types/permissions';

interface HRDashboardProps {
  permissions: string[];
}

interface StatItem {
  title: string;
  value: string | number;
  icon: React.ReactNode;
  permission: string;
}

interface RecentActivity {
  id: number;
  type: string;
  description: string;
  time: string;
}

const HRDashboard: React.FC<HRDashboardProps> = ({ permissions }) => {
  const stats: StatItem[] = [
    {
      title: 'Ứng viên mới',
      value: 5,
      icon: <UserAddOutlined />,
      permission: 'hr:recruitment:view'
    },
    {
      title: 'Hợp đồng sắp hết hạn',
      value: 3,
      icon: <FileTextOutlined />,
      permission: 'hr:employee:view'
    },
    {
      title: 'Khóa đào tạo đang diễn ra',
      value: 2,
      icon: <ReadOutlined />,
      permission: 'hr:training:view'
    },
    {
      title: 'Đăng ký phúc lợi mới',
      value: 4,
      icon: <DollarOutlined />,
      permission: 'hr:benefits:view'
    },
    {
      title: 'Nhân viên mới',
      value: 3,
      icon: <TeamOutlined />,
      permission: 'hr:employee:view'
    },
    {
      title: 'Chấm công hôm nay',
      value: '85%',
      icon: <ClockCircleOutlined />,
      permission: 'hr:employee:view'
    },
    {
      title: 'Thiết bị cần bảo trì',
      value: 2,
      icon: <ToolOutlined />,
      permission: 'hr:employee:view'
    },
    {
      title: 'Báo cáo tháng',
      value: 'Đã hoàn thành',
      icon: <BarChartOutlined />,
      permission: 'hr:employee:view'
    }
  ];

  const recentActivities: RecentActivity[] = [
    {
      id: 1,
      type: 'recruitment',
      description: 'Thêm ứng viên mới: Nguyễn Văn A',
      time: '2 giờ trước'
    },
    {
      id: 2,
      type: 'employee',
      description: 'Cập nhật thông tin nhân viên: Trần Thị B',
      time: '4 giờ trước'
    },
    {
      id: 3,
      type: 'training',
      description: 'Thêm khóa đào tạo mới: Kỹ năng giao tiếp',
      time: '1 ngày trước'
    }
  ];

  const columns: ColumnsType<RecentActivity> = [
    {
      title: 'Hoạt động',
      dataIndex: 'description',
      key: 'description'
    },
    {
      title: 'Thời gian',
      dataIndex: 'time',
      key: 'time'
    }
  ];

  return (
    <div className="hr-dashboard">
      <Row gutter={[16, 16]}>
        {stats.map((stat, index) => (
          permissions.includes(stat.permission) && (
            <Col xs={24} sm={12} md={6} key={index}>
              <Card>
                <Statistic
                  title={stat.title}
                  value={stat.value}
                  prefix={stat.icon}
                />
              </Card>
            </Col>
          )
        ))}
      </Row>

      <Row gutter={[16, 16]} style={{ marginTop: '24px' }}>
        <Col xs={24} md={12}>
          <Card title="Hoạt động gần đây">
            <Table<RecentActivity>
              dataSource={recentActivities}
              columns={columns}
              pagination={false}
              size="small"
            />
          </Card>
        </Col>
        <Col xs={24} md={12}>
          <Card title="Thống kê">
            <div style={{ height: '300px' }}>
              {/* Placeholder for charts */}
              <p>Biểu đồ thống kê sẽ được hiển thị ở đây</p>
            </div>
          </Card>
        </Col>
      </Row>

      <Row gutter={[16, 16]} style={{ marginTop: '24px' }}>
        <Col xs={24}>
          <Card title="Công việc cần xử lý">
            <Button type="primary" style={{ marginRight: '8px' }}>
              Duyệt đơn nghỉ phép
            </Button>
            <Button type="primary" style={{ marginRight: '8px' }}>
              Xử lý đăng ký phúc lợi
            </Button>
            <Button type="primary">
              Duyệt đăng ký đào tạo
            </Button>
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default HRDashboard; 