    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-indigo-400 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduConnect</span>
                    </div>
                    <p class="text-gray-400">Platform pembelajaran inovatif untuk pemerataan pendidikan di daerah 3T di Indonesia.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="../index.php" class="text-gray-400 hover:text-white">Beranda</a></li>
                        <li><a href="../kelas.php" class="text-gray-400 hover:text-white">Kelas</a></li>
                        <li><a href="../belanja.php" class="text-gray-400 hover:text-white">Belanja</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Tentang Kami</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Kebijakan</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Kebijakan Pengembalian</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Hubungi Kami</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-400"></i>
                            <span class="text-gray-400">hello@educonnect.id</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-2 text-gray-400"></i>
                            <span class="text-gray-400">+62 21 1234 5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                            <span class="text-gray-400">Jakarta, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>© <?php echo date('Y'); ?> EduConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Dropdown functionality
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const button = dropdown.querySelector('button');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            button.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });

        // Notification dropdown
        const notificationButton = document.getElementById('notification-button');
        if (notificationButton) {
            notificationButton.addEventListener('click', function() {
                document.getElementById('notification-dropdown').classList.toggle('hidden');
            });
        }
    </script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($pageSpecificJS)): ?>
        <script src="<?php echo $pageSpecificJS; ?>"></script>
    <?php endif; ?>
</body>
</html>