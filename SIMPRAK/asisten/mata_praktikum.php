<?php
require_once '../config.php';
$pageTitle = 'Manajemen Praktikum';
$activePage = 'mata_praktikum';
$asisten_id = $_SESSION['user_id'];

// Proses form (Tambah, Edit, Hapus)
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aksi Hapus
    if (isset($_POST['delete'])) {
        $id_to_delete = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ? AND asisten_id = ?");
        $stmt->bind_param("ii", $id_to_delete, $asisten_id);
        if ($stmt->execute()) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Praktikum berhasil dihapus.</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal menghapus praktikum.</div>';
        }
        $stmt->close();
    } 
    // Aksi Tambah atau Edit
    else {
        $id = $_POST['id'];
        $kode_praktikum = $_POST['kode_praktikum'];
        $nama_praktikum = $_POST['nama_praktikum'];
        $deskripsi = $_POST['deskripsi'];

        if (empty($id)) { // Tambah baru
            $stmt = $conn->prepare("INSERT INTO mata_praktikum (kode_praktikum, nama_praktikum, deskripsi, asisten_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $kode_praktikum, $nama_praktikum, $deskripsi, $asisten_id);
        } else { // Update
            $stmt = $conn->prepare("UPDATE mata_praktikum SET kode_praktikum = ?, nama_praktikum = ?, deskripsi = ? WHERE id = ? AND asisten_id = ?");
            $stmt->bind_param("sssii", $kode_praktikum, $nama_praktikum, $deskripsi, $id, $asisten_id);
        }

        if ($stmt->execute()) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Data praktikum berhasil disimpan.</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Ambil data untuk ditampilkan atau diedit
$praktikum_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ? AND asisten_id = ?");
    $stmt->bind_param("ii", $_GET['id'], $asisten_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $praktikum_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

// Ambil semua data praktikum
$praktikums = $conn->query("SELECT * FROM mata_praktikum WHERE asisten_id = $asisten_id ORDER BY nama_praktikum ASC");

require_once 'templates/header.php';
?>

<?php echo $message; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-xl font-bold mb-4"><?php echo $praktikum_to_edit ? 'Edit' : 'Tambah'; ?> Praktikum</h3>
            <form action="mata_praktikum.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $praktikum_to_edit['id'] ?? ''; ?>">
                <div class="mb-4">
                    <label for="kode_praktikum" class="block text-sm font-medium text-gray-700">Kode Praktikum</label>
                    <input type="text" name="kode_praktikum" id="kode_praktikum" value="<?php echo htmlspecialchars($praktikum_to_edit['kode_praktikum'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="nama_praktikum" class="block text-sm font-medium text-gray-700">Nama Praktikum</label>
                    <input type="text" name="nama_praktikum" id="nama_praktikum" value="<?php echo htmlspecialchars($praktikum_to_edit['nama_praktikum'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?php echo htmlspecialchars($praktikum_to_edit['deskripsi'] ?? ''); ?></textarea>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Simpan</button>
                    <?php if ($praktikum_to_edit): ?>
                        <a href="mata_praktikum.php" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-xl font-bold mb-4">Daftar Praktikum</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Kode</th>
                            <th class="py-2 px-4 text-left">Nama Praktikum</th>
                            <th class="py-2 px-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($praktikums->num_rows > 0): ?>
                            <?php while($row = $praktikums->fetch_assoc()): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($row['kode_praktikum']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                                    <td class="py-2 px-4 flex items-center space-x-2">
                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-1 px-2 rounded">Edit</a>
                                        <form action="mata_praktikum.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus praktikum ini?');" class="inline-block">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-4 px-4 text-center text-gray-500">Belum ada praktikum yang ditambahkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
