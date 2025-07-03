<?php
require_once '../config.php';
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
$mahasiswa_id = $_SESSION['user_id'];

$sql = "SELECT mp.*, u.nama as nama_asisten
        FROM mata_praktikum mp
        JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
        JOIN users u ON mp.asisten_id = u.id
        WHERE pp.mahasiswa_id = ?
        ORDER BY mp.nama_praktikum";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$my_courses = $stmt->get_result();

require_once 'templates/header_mahasiswa.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if ($my_courses->num_rows > 0): ?>
        <?php while ($course = $my_courses->fetch_assoc()): ?>
        <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="block bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($course['nama_praktikum']); ?></h3>
                <p class="text-sm text-gray-500 mb-4">Asisten: <?php echo htmlspecialchars($course['nama_asisten']); ?></p>
                <div class="flex items-center justify-end">
                    <span class="text-blue-600 font-semibold">Lihat Detail & Tugas &rarr;</span>
                </div>
            </div>
        </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="md:col-span-3 text-center p-8 bg-white rounded-lg shadow">
            <h3 class="text-xl text-gray-700">Anda belum mendaftar di praktikum manapun.</h3>
            <p class="text-gray-500 mt-2">Silakan cari dan daftar praktikum terlebih dahulu.</p>
            <a href="courses.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Cari Praktikum</a>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>