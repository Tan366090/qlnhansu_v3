<template>
  <div class="today-attendance">
    <h2>Danh sách chấm công hôm nay</h2>
    <div class="attendance-list">
      <table class="table">
        <thead>
          <tr>
            <th>Mã NV</th>
            <th>Họ tên</th>
            <th>Giờ vào</th>
            <th>Giờ ra</th>
            <th>Thời gian làm việc</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in attendanceList" :key="item.employee_id">
            <td>{{ item.employee_code }}</td>
            <td>{{ item.employee_name }}</td>
            <td>{{ formatTime(item.check_in_time) }}</td>
            <td>{{ formatTime(item.check_out_time) }}</td>
            <td>{{ item.work_duration_hours }} giờ</td>
            <td>
              <span :class="getStatusClass(item.attendance_symbol)">
                {{ getStatusText(item.attendance_symbol) }}
              </span>
            </td>
          </tr>
          <tr v-if="attendanceList.length === 0">
            <td colspan="6" class="text-center">Không có dữ liệu chấm công hôm nay</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TodayAttendance',
  data() {
    return {
      attendanceList: []
    }
  },
  created() {
    this.fetchTodayAttendance();
  },
  methods: {
    async fetchTodayAttendance() {
      try {
        const response = await axios.get('/api/attendance/today');
        if (response.data.status === 'success') {
          this.attendanceList = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching attendance:', error);
      }
    },
    formatTime(time) {
      if (!time) return '-';
      return time;
    },
    getStatusClass(symbol) {
      const classes = {
        'P': 'status-present',
        'A': 'status-absent',
        'L': 'status-leave',
        'WFH': 'status-wfh'
      };
      return classes[symbol] || 'status-unknown';
    },
    getStatusText(symbol) {
      const status = {
        'P': 'Có mặt',
        'A': 'Vắng mặt',
        'L': 'Nghỉ phép',
        'WFH': 'Làm việc từ xa'
      };
      return status[symbol] || 'Không xác định';
    }
  }
}
</script>

<style scoped>
.today-attendance {
  padding: 20px;
}

.attendance-list {
  margin-top: 20px;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.table th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.status-present {
  color: #28a745;
  font-weight: bold;
}

.status-absent {
  color: #dc3545;
  font-weight: bold;
}

.status-leave {
  color: #ffc107;
  font-weight: bold;
}

.status-wfh {
  color: #17a2b8;
  font-weight: bold;
}

.status-unknown {
  color: #6c757d;
  font-weight: bold;
}

.text-center {
  text-align: center;
}
</style> 