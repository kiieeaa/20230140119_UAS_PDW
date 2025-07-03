<?php
require_once '../config.php';
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
$asisten_id = $_SESSION['user_id'];
$message = '';

// Handle penilaian
if (isset($_POST['submit_nilai'])) {
    $laporan_id = $_POST['laporan_id'];
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ?, tanggal_nilai = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $nilai, $feedback, $laporan_id);
    if ($stmt->execute()) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Nilai berhasil disimpan.</div>';
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal menyimpan nilai.</div>';
    }
    $stmt->close();
}

// Base query
$sql = "SELECT l.id, u.nama as nama_mahasiswa, mp.nama_praktikum, m.judul_modul, l.file_laporan, l.tanggal_kumpul, l.nilai 
        FROM laporan l
        JOIN users u ON l.mahasiswa_id = u.id
        JOIN modul m ON l.modul_id = m.id
        JOIN mata_praktikum mp ON m.praktikum_id = mp.id
        WHERE mp.asisten_id = $asisten_id";

// Filtering logic
$filter_praktikum = $_GET['filter_praktikum'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
if (!empty($filter_praktikum)) {
    $sql .= " AND mp.id = " . intval($filter_praktikum);
}
if ($filter_status == 'dinilai') {
    $sql .= " AND l.nilai IS NOT NULL";
} elseif ($filter_status == 'belum_dinilai') {
    $sql .= " AND l.nilai IS NULL";
}
$sql .= " ORDER BY l.tanggal_kumpul DESC";

$laporans = $conn->query($sql);
$praktikums = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum WHERE asisten_id = $asisten_id");

require_once 'templates/header.php';
?>

<?php echo $message; ?>

<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-xl font-bold mb-4">Filter Laporan</h3>
    <form action="laporan.php" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="filter_praktikum" class="block text-sm font-medium text-gray-700">Praktikum</label>
                <select name="filter_praktikum" id="filter_praktikum" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Semua Praktikum</option>
                    <?php while ($p = $praktikums->fetch_assoc()) {
                        $selected = ($filter_praktikum == $p['id']) ? 'selected' : '';
                        echo "<option value='{$p['id']}' $selected>" . htmlspecialchars($p['nama_praktikum']) . "</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label for="filter_status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="filter_status" id="filter_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="" <?php echo ($filter_status == '') ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
                    <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
                </select>
            </div>
            <div class="self-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow mt-6">
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">Mahasiswa</th>
                    <th class="py-2 px-4 text-left">Praktikum / Modul</th>
                    <th class="py-2 px-4 text-left">Tgl Kumpul</th>
                    <th class="py-2 px-4 text-left">Status</th>
                    <th class="py-2 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($laporans && $laporans->num_rows > 0): ?>
                    <?php while ($row = $laporans->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                        <td class="py-2 px-4">
                            <span class="font-semibold"><?php echo htmlspecialchars($row['nama_praktikum']); ?></span><br>
                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($row['judul_modul']); ?></span>
                        </td>
                        <td class="py-2 px-4"><?php echo date('d M Y H:i', strtotime($row['tanggal_kumpul'])); ?></td>
                        <td class="py-2 px-4">
                            <?php if ($row['nilai'] !== null): ?>
                                <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Dinilai (<?php echo $row['nilai']; ?>)</span>
                            <?php else: ?>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Menunggu</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2 px-4">
                             <a href="../uploads/laporan/<?php echo $row['file_laporan']; ?>" target="_blank" class="text-blue-600 hover:underline">Unduh</a>
                        </td>
                    </tr>
                    <?php if ($row['nilai'] === null): ?>
                    <tr class="border-b bg-gray-50">
                        <td colspan="5" class="p-4">
                            <form method="POST" action="laporan.php">
                                <input type="hidden" name="laporan_id" value="<?php echo $row['id']; ?>">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-grow">
                                        <label for="feedback_<?php echo $row['id']; ?>" class="text-sm font-medium">Feedback</label>
                                        <input type="text" name="feedback" id="feedback_<?php echo $row['id']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label for="nilai_<?php echo $row['id']; ?>" class="text-sm font-medium">Nilai</label>
                                        <input type="number" name="nilai" id="nilai_<?php echo $row['id']; ?>" required min="0" max="100" class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <button type="submit" name="submit_nilai" class="mt-5 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-3 rounded-md text-sm">Simpan Nilai</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-gray-500">Tidak ada laporan yang ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>