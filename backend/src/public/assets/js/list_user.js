document.addEventListener("DOMContentLoaded", function () {
    const userTableBody = document.getElementById("userTableBody");

    // Sample user data
    const users = [
        {
            id: 1,
            name: "Nguyễn Văn A",
            dob: "1990-01-15",
            email: "vana@gmail.com",
            phone: "0981 234 567",
            address: "123 Đường ABC, Quận 1, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 2,
            name: "Trần Thị B",
            dob: "1985-03-20",
            email: "tranb@example.com",
            phone: "0902 345 678",
            address: "456 Đường DEF, Quận 3, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Khóa",
        },
        {
            id: 3,
            name: "Lê Văn C",
            dob: "1992-07-10",
            email: "levanc@example.com",
            phone: "0912 456 789",
            address: "789 Đường GHI, Quận 5, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 4,
            name: "Phạm Thị D",
            dob: "1988-11-25",
            email: "phamtd@example.com",
            phone: "0933 567 890",
            address: "321 Đường JKL, Quận 7, TP. Hồ Chí Minh",
            role: "Admin",
            status: "Hoạt động",
        },
        {
            id: 5,
            name: "Hoàng Văn E",
            dob: "1995-05-30",
            email: "hoange@example.com",
            phone: "0944 678 901",
            address: "654 Đường MNO, Quận 9, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 6,
            name: "Ngô Thị F",
            dob: "1993-09-12",
            email: "ngothif@example.com",
            phone: "0955 789 012",
            address: "987 Đường PQR, Quận 2, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 7,
            name: "Đặng Văn G",
            dob: "1987-04-18",
            email: "dangvg@example.com",
            phone: "0966 890 123",
            address: "123 Đường STU, Quận 4, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Khóa",
        },
        {
            id: 8,
            name: "Vũ Thị H",
            dob: "1991-06-22",
            email: "vuth@example.com",
            phone: "0977 901 234",
            address: "456 Đường VWX, Quận 6, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 9,
            name: "Nguyễn Thị I",
            dob: "1994-08-05",
            email: "nguyenthii@example.com",
            phone: "0988 012 345",
            address: "789 Đường YZ, Quận 8, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 10,
            name: "Phan Văn J",
            dob: "1989-02-14",
            email: "phanvj@example.com",
            phone: "0999 123 456",
            address: "321 Đường ABC, Quận 10, TP. Hồ Chí Minh",
            role: "Admin",
            status: "Hoạt động",
        },
        {
            id: 11,
            name: "Nguyễn Văn K",
            dob: "1991-03-12",
            email: "nguyenvank@example.com",
            phone: "0911 234 567",
            address: "123 Đường LMN, Quận 11, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 12,
            name: "Trần Thị L",
            dob: "1986-07-19",
            email: "tranthil@example.com",
            phone: "0903 456 789",
            address: "456 Đường OPQ, Quận 12, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Khóa",
        },
        {
            id: 13,
            name: "Lê Văn M",
            dob: "1993-05-22",
            email: "levanm@example.com",
            phone: "0914 567 890",
            address: "789 Đường RST, Quận 2, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 14,
            name: "Phạm Thị N",
            dob: "1989-09-30",
            email: "phamthil@example.com",
            phone: "0935 678 901",
            address: "321 Đường UVW, Quận 3, TP. Hồ Chí Minh",
            role: "Admin",
            status: "Hoạt động",
        },
        {
            id: 15,
            name: "Hoàng Văn O",
            dob: "1996-01-10",
            email: "hoangvo@example.com",
            phone: "0946 789 012",
            address: "654 Đường XYZ, Quận 4, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 16,
            name: "Ngô Thị P",
            dob: "1994-04-15",
            email: "ngothip@example.com",
            phone: "0957 890 123",
            address: "987 Đường ABC, Quận 5, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 17,
            name: "Đặng Văn Q",
            dob: "1988-08-20",
            email: "dangvq@example.com",
            phone: "0968 901 234",
            address: "123 Đường DEF, Quận 6, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Khóa",
        },
        {
            id: 18,
            name: "Vũ Thị R",
            dob: "1992-12-25",
            email: "vuthir@example.com",
            phone: "0979 012 345",
            address: "456 Đường GHI, Quận 7, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 19,
            name: "Nguyễn Thị S",
            dob: "1995-06-18",
            email: "nguyenthis@example.com",
            phone: "0980 123 456",
            address: "789 Đường JKL, Quận 8, TP. Hồ Chí Minh",
            role: "Người dùng",
            status: "Hoạt động",
        },
        {
            id: 20,
            name: "Phan Văn T",
            dob: "1990-11-11",
            email: "phanvt@example.com",
            phone: "0991 234 567",
            address: "321 Đường MNO, Quận 9, TP. Hồ Chí Minh",
            role: "Admin",
            status: "Hoạt động",
        },
    ];

    // Render user data into the table
    function renderUsers() {
        userTableBody.innerHTML = ""; // Clear existing rows
        users.forEach((user, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${user.name}</td>
                <td>${user.dob}</td>
                <td>${user.email}</td>
                <td>${user.phone}</td>
                <td>${user.address}</td>
                <td>${user.role}</td>
                <td>${user.status}</td>
                <td class="action-buttons">
                    <button class="edit-btn">✏️ Sửa</button>
                    <button class="delete-btn">❌ Xóa</button>
                </td>
            `;
            userTableBody.appendChild(row);
        });
    }

    renderUsers();
});
