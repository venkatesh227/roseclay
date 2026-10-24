            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.admin-table').DataTable({
                "pageLength": 10,
                "order": [[ 0, "desc" ]], // Default sort by first column (usually ID or Date) descending
                "language": {
                    "search": "Filter records:",
                    "lengthMenu": "Show _MENU_ entries"
                },
                "columnDefs": [
                    { "orderable": false, "targets": -1 } // Disable sorting on the last column (Actions)
                ]
            });
        });

        function toggleSidebar() {
            document.querySelector('.admin-sidebar').classList.toggle('active');
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
