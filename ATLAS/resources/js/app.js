import './bootstrap';

// Sidebar Toggle on Mobile/Tablet
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        // Toggle sidebar on button click
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside of it (but not on the toggle button)
        document.addEventListener('click', function (e) {
            // Check if click is outside sidebar and not on the toggle button
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnToggle = sidebarToggle.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle) {
                sidebar.classList.remove('active');
            }
        });

        // Close sidebar when clicking a nav link
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                sidebar.classList.remove('active');
            });
        });
    }
});
