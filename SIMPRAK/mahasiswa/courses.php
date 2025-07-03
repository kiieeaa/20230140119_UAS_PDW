<?php
require_once '../config.php';
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
$mahasiswa_id = $_SESSION['user_id'];
$message = '';

// Handle pendaftaran
if (isset($_POST['daftar'])) {
    $praktikum_id = $_POST['praktikum_id'];
    
    // Cek dulu apakah sudah terdaftar
    $cek_stmt = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $cek_stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $cek_stmt->execute();
    $cek_stmt->store_result();

    if ($cek_stmt->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
        if ($stmt->execute()) {
             $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Berhasil mendaftar praktikum!</div>';
        } else {
             $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Gagal mendaftar.</div>';
        }
        $stmt->close();
    }
    $cek_stmt->close();
}


// Ambil semua praktikum yang tersedia dan info asisten
$sql = "SELECT mp.*, u.nama as nama_asisten, 
        (SELECT COUNT(*) FROM pendaftaran_praktikum WHERE praktikum_id = mp.id AND mahasiswa_id = $mahasiswa_id) as terdaftar
        FROM mata_praktikum mp 
        JOIN users u ON mp.asisten_id = u.id 
        ORDER BY mp.nama_praktikum";
$courses = $conn->query($sql);

require_once 'templates/header_mahasiswa.php';
?>

<?php echo $message; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if ($courses->num_rows > 0): ?>
        <?php while ($course = $courses->fetch_assoc()): ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($course['nama_praktikum']); ?></h3>
                <p class="text-sm text-gray-500 mb-2">Kode: <?php echo htmlspecialchars($course['kode_praktikum']); ?></p>
                <p class="text-gray-600 text-sm mb-4 h-16 overflow-y-auto"><?php echo htmlspecialchars($course['deskripsi']); ?></p>
                <p class="text-sm text-gray-500 mb-4">Asisten: <?php echo htmlspecialchars($course['nama_asisten']); ?></p>
                
                <?php if ($course['terdaftar'] > 0): ?>
                    <button disabled class="w-full bg-green-500 text-white font-bold py-2 px-4 rounded opacity-50 cursor-not-allowed">
                        Terdaftar
                    </button>
                <?php else: ?>
                    <form method="POST" action="courses.php">
                        <input type="hidden" name="praktikum_id" value="<?php echo $course['id']; ?>">
                        <button type="submit" name="daftar" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Daftar Praktikum
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-gray-600 md:col-span-3 text-center">Tidak ada praktikum yang tersedia saat ini.</p>
    <?php endif; ?>
</div>


<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>