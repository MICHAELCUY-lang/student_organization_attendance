</main>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });
            
            // Initialize date pickers
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
            
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
            
            // Sidebar toggle for mobile
            $('#sidebarToggle').on('click', function() {
                $('.sidebar').toggleClass('show');
            });
            
            // Delete confirmation using SweetAlert2
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).attr('href');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef476f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').not('.alert-permanent').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            
            // Click outside to close sidebar on mobile
            $(document).on('click', function(e) {
                if ($(window).width() < 992) {
                    if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('#sidebarToggle').length) {
                        $('.sidebar').removeClass('show');
                    }
                }
            });
        });
    </script>
</body>
</html>