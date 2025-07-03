<?php
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];

// 1. Hitung total praktikum yang diikuti
$stmt_praktikum = $conn->prepare("SELECT COUNT(id) as total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?");
$stmt_praktikum->bind_param("i", $mahasiswa_id);
$stmt_praktikum->execute();
$total_praktikum = $stmt_praktikum->get_result()->fetch_assoc()['total'];

// 2. Hitung total laporan yang sudah dikumpulkan
$stmt_laporan = $conn->prepare("SELECT COUNT(id) as total FROM laporan WHERE mahasiswa_id = ?");
$stmt_laporan->bind_param("i", $mahasiswa_id);
$stmt_laporan->execute();
$total_laporan_terkumpul = $stmt_laporan->get_result()->fetch_assoc()['total'];

// 3. Hitung total laporan yang sudah dinilai
$stmt_dinilai = $conn->prepare("SELECT COUNT(id) as total FROM laporan WHERE mahasiswa_id = ? AND nilai IS NOT NULL");
$stmt_dinilai->bind_param("i", $mahasiswa_id);
$stmt_dinilai->execute();
$total_laporan_dinilai = $stmt_dinilai->get_result()->fetch_assoc()['total'];

// 4. Ambil aktivitas nilai terbaru (5 terakhir)
$stmt_aktivitas = $conn->prepare("
    SELECT l.nilai, l.tanggal_nilai, m.judul_modul, mp.nama_praktikum
    FROM laporan l
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.praktikum_id = mp.id
    WHERE l.mahasiswa_id = ? AND l.nilai IS NOT NULL
    ORDER BY l.tanggal_nilai DESC
    LIMIT 5
");
$stmt_aktivitas->bind_param("i", $mahasiswa_id);
$stmt_aktivitas->execute();
$aktivitas_terbaru = $stmt_aktivitas->get_result();
?>

<div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
    <p class="text-gray-600 mt-1">Ini adalah ringkasan aktivitas praktikum Anda.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow flex items-center">
        <div class="bg-blue-100 p-3 rounded-full mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Praktikum Diikuti</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_praktikum; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow flex items-center">
        <div class="bg-green-100 p-3 rounded-full mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Terkumpul</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan_terkumpul; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow flex items-center">
        <div class="bg-indigo-100 p-3 rounded-full mr-4">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow mt-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Nilai Terbaru</h3>
    <div class="space-y-4">
        <?php if ($aktivitas_terbaru->num_rows > 0): ?>
            <?php while($aktivitas = $aktivitas_terbaru->fetch_assoc()): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div>
                    <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($aktivitas['nama_praktikum']); ?></p>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></p>
                </div>
                <div class="text-right">
                     <p class="text-lg font-bold text-green-600"><?php echo htmlspecialchars($aktivitas['nilai']); ?></p>
                     <p class="text-xs text-gray-400">Dinilai pada <?php echo date('d M Y', strtotime($aktivitas['tanggal_nilai'])); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">Belum ada laporan yang dinilai oleh asisten.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt_praktikum->close();
$stmt_laporan->close();
$stmt_dinilai->close();
$stmt_aktivitas->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>