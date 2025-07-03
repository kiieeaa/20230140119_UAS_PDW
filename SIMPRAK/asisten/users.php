<?php
require_once '../config.php';
$pageTitle = 'Manajemen Pengguna';
$activePage = 'users';
$current_asisten_id = $_SESSION['user_id'];
$message = '';

// Tampilkan pesan dari session jika ada (setelah redirect)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Proses form (Tambah, Edit, Hapus)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Aksi Hapus
    if (isset($_POST['delete'])) {
        $id_to_delete = $_POST['id'];
        // Keamanan: Pastikan asisten tidak menghapus akunnya sendiri
        if ($id_to_delete == $current_asisten_id) {
            $_SESSION['message'] = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">Anda tidak dapat menghapus akun Anda sendiri.</div>';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                $_SESSION['message'] = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Pengguna berhasil dihapus.</div>';
            } else {
                $_SESSION['message'] = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Gagal menghapus pengguna.</div>';
            }
            $stmt->close();
        }
    } 
    // Aksi Tambah atau Edit
    else {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'];

        if (empty($id)) { // Tambah Pengguna Baru
            if (empty($password)) {
                 $_SESSION['message'] = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Password wajib diisi untuk pengguna baru.</div>';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama, $email, $hashed_password, $role);
                if ($stmt->execute()) {
                    $_SESSION['message'] = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Pengguna baru berhasil ditambahkan.</div>';
                } else {
                    $_SESSION['message'] = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Error: ' . $stmt->error . '</div>';
                }
                $stmt->close();
            }
        } else { // Update Pengguna
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $nama, $email, $role, $hashed_password, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nama, $email, $role, $id);
            }
            if ($stmt->execute()) {
                $_SESSION['message'] = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Data pengguna berhasil diperbarui.</div>';
            } else {
                $_SESSION['message'] = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }
    // Redirect untuk menghindari resubmit form (Pola PRG)
    header("Location: users.php");
    exit();
}

// Ambil data untuk form edit
$user_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

// Ambil semua data pengguna
$users = $conn->query("SELECT id, nama, email, role FROM users ORDER BY role, nama ASC");

require_once 'templates/header.php';
?>

<?php echo $message; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-xl font-bold mb-4"><?php echo $user_to_edit ? 'Edit' : 'Tambah'; ?> Pengguna</h3>
            <form action="users.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $user_to_edit['id'] ?? ''; ?>">
                <div class="mb-4">
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="<?php echo htmlspecialchars($user_to_edit['nama'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                 <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_to_edit['email'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700">Peran (Role)</label>
                    <select name="role" id="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="mahasiswa" <?php echo (($user_to_edit['role'] ?? '') == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                        <option value="asisten" <?php echo (($user_to_edit['role'] ?? '') == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <?php if ($user_to_edit): ?>
                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password.</p>
                    <?php else: ?>
                         <p class="mt-1 text-xs text-gray-500">Wajib diisi untuk pengguna baru.</p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Simpan</button>
                    <?php if ($user_to_edit): ?>
                        <a href="users.php" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-xl font-bold mb-4">Daftar Pengguna</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Nama</th>
                            <th class="py-2 px-4 text-left">Email</th>
                            <th class="py-2 px-4 text-left">Role</th>
                            <th class="py-2 px-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while($row = $users->fetch_assoc()): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="py-2 px-4">
                                        <span class="capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['role'] == 'asisten' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo htmlspecialchars($row['role']); ?>
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 flex items-center space-x-2">
                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-1 px-2 rounded">Edit</a>
                                        <?php if ($row['id'] != $current_asisten_id): // Cegah tombol hapus muncul untuk diri sendiri ?>
                                        <form action="users.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus pengguna ini?');" class="inline-block">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 px-4 text-center text-gray-500">Tidak ada pengguna yang terdaftar.</td>
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