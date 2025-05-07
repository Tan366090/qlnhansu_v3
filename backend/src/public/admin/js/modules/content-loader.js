export class ContentLoader {
    static async loadContent(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const content = await response.text();
            document.getElementById('mainContent').innerHTML = content;
            
            // Cập nhật URL mà không reload trang
            history.pushState({}, '', url);
            
            // Thêm class active cho menu item được chọn
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === url) {
                    link.classList.add('active');
                }
            });
        } catch (error) {
            console.error('Error loading content:', error);
            document.getElementById('mainContent').innerHTML = `
                <div class="alert alert-danger">
                    Có lỗi xảy ra khi tải nội dung. Vui lòng thử lại sau.
                </div>
            `;
        }
    }

    static init() {
        // Xử lý sự kiện click cho tất cả các menu link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                ContentLoader.loadContent(url);
            });
        });

        // Xử lý sự kiện popstate (khi người dùng nhấn nút back/forward)
        window.addEventListener('popstate', (e) => {
            ContentLoader.loadContent(window.location.pathname);
        });
    }
} 