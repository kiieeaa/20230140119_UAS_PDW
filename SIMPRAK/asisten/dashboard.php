<?php
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php';

$asisten_id = $_SESSION['user_id'];

// Hitung total praktikum yang diampu
$stmt_praktikum = $conn->prepare("SELECT COUNT(*) as total FROM mata_praktikum WHERE asisten_id = ?");
$stmt_praktikum->bind_param("i", $asisten_id);
$stmt_praktikum->execute();
$total_praktikum = $stmt_praktikum->get_result()->fetch_assoc()['total'];

// Hitung total laporan masuk untuk praktikum yang diampu
$stmt_laporan = $conn->prepare("
    SELECT COUNT(l.id) as total 
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
    WHERE mp.asisten_id = ?
");
$stmt_laporan->bind_param("i", $asisten_id);
$stmt_laporan->execute();
$total_laporan = $stmt_laporan->get_result()->fetch_assoc()['total'];

// Hitung total laporan yang belum dinilai
$stmt_unrated = $conn->prepare("
    SELECT COUNT(l.id) as total 
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
    WHERE mp.asisten_id = ? AND l.nilai IS NULL
");
$stmt_unrated->bind_param("i", $asisten_id);
$stmt_unrated->execute();
$laporan_belum_dinilai = $stmt_unrated->get_result()->fetch_assoc()['total'];

// Ambil aktivitas laporan terbaru
$stmt_aktivitas = $conn->prepare("
    SELECT u.nama, mo.judul_modul, l.tanggal_kumpul 
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul mo ON l.modul_id = mo.id
    JOIN mata_praktikum mp ON mo.praktikum_id = mp.id
    WHERE mp.asisten_id = ?
    ORDER BY l.tanggal_kumpul DESC
    LIMIT 5
");
$stmt_aktivitas->bind_param("i", $asisten_id);
$stmt_aktivitas->execute();
$aktivitas_terbaru = $stmt_aktivitas->get_result();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow flex items-center">
        <div class="bg-blue-100 p-3 rounded-full mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h6m-6 4h6m-6 4h6M4 21V5a2 2 0 012-2h12a2 2 0 012 2v16"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Praktikum Diampu</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_praktikum; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow flex items-center">
        <div class="bg-green-100 p-3 rounded-full mr-4">
           <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow flex items-center">
        <div class="bg-yellow-100 p-3 rounded-full mr-4">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if ($aktivitas_terbaru->num_rows > 0): ?>
            <?php while($aktivitas = $aktivitas_terbaru->fetch_assoc()): ?>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                    <span class="font-bold text-gray-500"><?php echo strtoupper(substr($aktivitas['nama'], 0, 2)); ?></span>
                </div>
                <div>
                    <p class="text-gray-800"><strong><?php echo htmlspecialchars($aktivitas['nama']); ?></strong> mengumpulkan laporan untuk <strong><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></strong></p>
                    <p class="text-sm text-gray-500"><?php echo date('d M Y, H:i', strtotime($aktivitas['tanggal_kumpul'])); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500">Belum ada aktivitas laporan.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt_praktikum->close();
$stmt_laporan->close();
$stmt_unrated->close();
$stmt_aktivitas->close();
$conn->close();
require_once 'templates/footer.php';
?>