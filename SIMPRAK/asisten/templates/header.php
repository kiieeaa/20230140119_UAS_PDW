<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <title>Panel Asisten - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
<div class="min-h-full">
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-white font-bold text-xl">SIMPRAK - Asisten</h1>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                                $navItems = [
                                    'dashboard' => 'Dashboard',
                                    'mata_praktikum' => 'Praktikum',
                                    'modul' => 'Modul',
                                    'laporan' => 'Laporan',
                                    'users' => 'Pengguna'
                                ];
                                $activeClass = 'bg-gray-900 text-white';
                                $inactiveClass = 'text-gray-300 hover:bg-gray-700 hover:text-white';
                            ?>
                            <?php foreach ($navItems as $file => $name): ?>
                                <a href="<?php echo $file; ?>.php" class="<?php echo ($activePage == $file) ? $activeClass : $inactiveClass; ?> rounded-md px-3 py-2 text-sm font-medium"><?php echo $name; ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="text-gray-400 mr-4">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                        <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md text-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <header class="bg-white shadow">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        </div>
    </header>

    <main>
        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            
