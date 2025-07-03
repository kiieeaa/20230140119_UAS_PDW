<?php
require_once '../config.php';
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
$asisten_id = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $praktikum_id = $_POST['praktikum_id'];
    $judul_modul = $_POST['judul_modul'];
    $deskripsi_modul = $_POST['deskripsi_modul'];
    $id = $_POST['id'] ?? null;
    $file_materi = $_POST['existing_file'] ?? null;

    // Handle file upload
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
        $target_dir = "../uploads/materi/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $filename = uniqid() . '_' . basename($_FILES["file_materi"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
            $file_materi = $filename;
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal mengunggah file.</div>';
        }
    }

    if (empty($message)) {
        if ($id) { // Update
            $stmt = $conn->prepare("UPDATE modul SET praktikum_id = ?, judul_modul = ?, deskripsi_modul = ?, file_materi = ? WHERE id = ?");
            $stmt->bind_param("isssi", $praktikum_id, $judul_modul, $deskripsi_modul, $file_materi, $id);
        } else { // Insert
            $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $praktikum_id, $judul_modul, $deskripsi_modul, $file_materi);
        }
        if ($stmt->execute()) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Modul berhasil disimpan.</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: '. $stmt->error .'</div>';
        }
        $stmt->close();
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
         $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Modul berhasil dihapus.</div>';
    } else {
         $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal menghapus modul.</div>';
    }
    $stmt->close();
}


// Get data for form & table
$praktikums = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum WHERE asisten_id = $asisten_id");
$moduls = $conn->query("SELECT m.*, mp.nama_praktikum FROM modul m JOIN mata_praktikum mp ON m.praktikum_id = mp.id WHERE mp.asisten_id = $asisten_id ORDER BY mp.nama_praktikum, m.id");

require_once 'templates/header.php';
?>

<?php echo $message; ?>

<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-xl font-bold mb-4">Tambah/Edit Modul</h3>
    <form action="modul.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value=""> <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="praktikum_id" class="block text-sm font-medium text-gray-700">Praktikum</label>
                <select name="praktikum_id" id="praktikum_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">-- Pilih Praktikum --</option>
                    <?php while ($p = $praktikums->fetch_assoc()) {
                        echo "<option value='{$p['id']}'>" . htmlspecialchars($p['nama_praktikum']) . "</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label for="judul_modul" class="block text-sm font-medium text-gray-700">Judul Modul</label>
                <input type="text" name="judul_modul" id="judul_modul" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="md:col-span-2">
                <label for="deskripsi_modul" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="deskripsi_modul" id="deskripsi_modul" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <div>
                <label for="file_materi" class="block text-sm font-medium text-gray-700">File Materi (PDF/DOCX)</label>
                <input type="file" name="file_materi" id="file_materi" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Simpan Modul</button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow mt-6">
    <h3 class="text-xl font-bold mb-4">Daftar Modul</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">Praktikum</th>
                    <th class="py-2 px-4 text-left">Judul Modul</th>
                    <th class="py-2 px-4 text-left">File</th>
                    <th class="py-2 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($moduls->num_rows > 0): ?>
                    <?php while ($row = $moduls->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($row['judul_modul']); ?></td>
                        <td class="py-2 px-4">
                            <?php if ($row['file_materi']): ?>
                                <a href="../uploads/materi/<?php echo $row['file_materi']; ?>" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="py-2 px-4">
                            <a href="?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus modul ini?');" class="text-red-600 hover:underline">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4 text-gray-500">Belum ada modul.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>