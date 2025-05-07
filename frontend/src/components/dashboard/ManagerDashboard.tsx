import React from 'react';
import { Card, Row, Col, Statistic, Table, Button } from 'antd';
import type { ColumnsType } from 'antd/es/table';
import type { ColProps } from 'antd/es/col';
import { 
  TeamOutlined, 
  CheckCircleOutlined, 
  MoneyCollectOutlined,
  ProjectOutlined,
  BarChartOutlined,
  ToolOutlined,
  CalendarOutlined
} from '@ant-design/icons';
import { MANAGER_PERMISSIONS } from '../../types/permissions';

interface ManagerDashboardProps {
  permissions: string[];
}

interface StatItem {
  title: string;
  value: string | number;
  icon: React.ReactNode;
  permission: string;
}

interface TeamPerformance {
  name: string;
  progress: number;
  target: number;
}

interface RecentActivity {
  id: number;
  type: string;
  description: string;
  time: string;
}

const ManagerDashboard: React.FC<ManagerDashboardProps> = ({ permissions }) => {
  const stats: StatItem[] = [
    {
      title: 'Tổng số nhân viên',
      value: 25,
      icon: <TeamOutlined />,
      permission: 'manager:employee:view'
    },
    {
      title: 'Tỷ lệ chấm công hôm nay',
      value: '85%',
      icon: <CheckCircleOutlined />,
      permission: 'manager:employee:view'
    },
    {
      title: 'Tổng quỹ lương tháng',
      value: '890.5M',
      icon: <MoneyCollectOutlined />,
      permission: 'manager:employee:view'
    },
    {
      title: 'Dự án đang thực hiện',
      value: 3,
      icon: <ProjectOutlined />,
      permission: 'manager:project:view'
    }
  ];

  const teamPerformance: TeamPerformance[] = [
    {
      name: 'Nhóm A',
      progress: 85,
      target: 90
    },
    {
      name: 'Nhóm B',
      progress: 75,
      target: 85
    },
    {
      name: 'Nhóm C',
      progress: 92,
      target: 95
    }
  ];

  const recentActivities: RecentActivity[] = [
    {
      id: 1,
      type: 'evaluation',
      description: 'Đánh giá nhân viên: Nguyễn Văn A',
      time: '2 giờ trước'
    },
    {
      id: 2,
      type: 'project',
      description: 'Cập nhật tiến độ dự án X',
      time: '4 giờ trước'
    },
    {
      id: 3,
      type: 'leave',
      description: 'Duyệt đơn nghỉ phép: Trần Thị B',
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
    <div className="manager-dashboard">
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
          <Card title="Hiệu suất nhóm">
            {teamPerformance.map((team, index) => (
              <div key={index} style={{ marginBottom: '16px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                  <span>{team.name}</span>
                  <span>{team.progress}% / {team.target}%</span>
                </div>
                <div style={{ width: '100%', height: '8px', backgroundColor: '#f0f0f0', borderRadius: '4px' }}>
                  <div 
                    style={{ 
                      width: `${team.progress}%`, 
                      height: '100%', 
                      backgroundColor: team.progress >= team.target ? '#52c41a' : '#1890ff',
                      borderRadius: '4px'
                    }} 
                  />
                </div>
              </div>
            ))}
          </Card>
        </Col>
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
      </Row>

      <Row gutter={[16, 16]} style={{ marginTop: '24px' }}>
        <Col xs={24}>
          <Card title="Công việc cần xử lý">
            <Button type="primary" style={{ marginRight: '8px' }}>
              Duyệt đơn nghỉ phép
            </Button>
            <Button type="primary" style={{ marginRight: '8px' }}>
              Đánh giá nhân viên
            </Button>
            <Button type="primary">
              Cập nhật tiến độ dự án
            </Button>
          </Card>
        </Col>
      </Row>

      <Row gutter={[16, 16]} style={{ marginTop: '24px' }}>
        <Col xs={24}>
          <Card title="Thống kê dự án">
            <div style={{ height: '300px' }}>
              {/* Placeholder for project charts */}
              <p>Biểu đồ thống kê dự án sẽ được hiển thị ở đây</p>
            </div>
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default ManagerDashboard; 