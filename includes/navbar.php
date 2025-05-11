<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek status login dan role user
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : null;
$userName = $isLoggedIn ? $_SESSION['full_name'] : null;
?>

<!-- Navbar -->
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo dan Brand -->
            <div class="flex items-center">
                <a href="/" class="flex items-center">
                    <i class="fas fa-graduation-cap text-primary text-2xl mr-2"></i>
                    <span class="text-xl font-bold">EduConnect</span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="/kelas.php" class="text-gray-700 hover:text-primary font-medium">
                    <i class="fas fa-graduation-cap mr-1"></i>Kelas
                </a>
                <a href="/mission.php" class="text-gray-700 hover:text-primary font-medium">
                    <i class="fas fa-tasks mr-1"></i>Misi
                </a>
                <a href="/community.php" class="text-gray-700 hover:text-primary font-medium">
                    <i class="fas fa-users mr-1"></i>Komunitas
                </a>
                <a href="/mentoring.php" class="text-gray-700 hover:text-primary font-medium">
                    <i class="fas fa-chalkboard-teacher mr-1"></i>Mentoring
                </a>
                
                <?php if ($isLoggedIn): ?>
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-primary focus:outline-none">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="hidden lg:inline"><?php echo htmlspecialchars($userName); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <?php if ($userRole === 'admin'): ?>
                            <a href="/dashboardadmin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/auth/login.php" class="text-gray-700 hover:text-primary font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-700 hover:text-primary focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="/kelas.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                <i class="fas fa-graduation-cap mr-2"></i>Kelas
            </a>
            <a href="/mission.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                <i class="fas fa-tasks mr-2"></i>Misi
            </a>
            <a href="/community.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                <i class="fas fa-users mr-2"></i>Komunitas
            </a>
            <a href="/mentoring.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                <i class="fas fa-chalkboard-teacher mr-2"></i>Mentoring
            </a>
            
            <?php if ($isLoggedIn): ?>
                <hr class="my-2">
                <a href="/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-user mr-2"></i>Profil
                </a>
                <?php if ($userRole === 'admin'): ?>
                <a href="/dashboardadmin.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-cog mr-2"></i>Admin Panel
                </a>
                <?php endif; ?>
                <a href="auth/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            <?php else: ?>
                <a href="/auth/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Script untuk Mobile Menu -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
    });
});
</script> 