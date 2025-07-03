<?php
require_once '../config.php';
$mahasiswa_id = $_SESSION['user_id'];
$praktikum_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

// Handle upload laporan
if (isset($_POST['submit_laporan'])) {
    $modul_id = $_POST['modul_id'];
    
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $target_dir = "../uploads/laporan/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        $filename = uniqid() . '_' . basename($_FILES["file_laporan"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $modul_id, $mahasiswa_id, $filename);
            if ($stmt->execute()) {
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Laporan berhasil diunggah.</div>';
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Gagal menyimpan data laporan.</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Gagal mengunggah file.</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Pilih file untuk diunggah.</div>';
    }
}


// Get course details
$stmt_course = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
$stmt_course->bind_param("i", $praktikum_id);
$stmt_course->execute();
$course = $stmt_course->get_result()->fetch_assoc();
$pageTitle = htmlspecialchars($course['nama_praktikum'] ?? 'Detail Praktikum');
$activePage = 'my_courses';

// Get modules for this course, along with submission status and grade
$sql_modul = "SELECT m.*, l.file_laporan, l.nilai, l.feedback 
              FROM modul m 
              LEFT JOIN laporan l ON m.id = l.modul_id AND l.mahasiswa_id = ?
              WHERE m.praktikum_id = ? 
              ORDER BY m.id";
$stmt_modul = $conn->prepare($sql_modul);
$stmt_modul->bind_param("ii", $mahasiswa_id, $praktikum_id);
$stmt_modul->execute();
$moduls = $stmt_modul->get_result();

require_once 'templates/header_mahasiswa.php';
?>

<?php echo $message; ?>

<div class="space-y-6">
    <?php if ($moduls->num_rows > 0): ?>
        <?php while ($modul = $moduls->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <div class="md:col-span-1">
                        <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($modul['judul_modul']); ?></h4>
                        <p class="text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($modul['deskripsi_modul']); ?></p>
                        <?php if ($modul['file_materi']): ?>
                            <a href="../uploads/materi/<?php echo $modul['file_materi']; ?>" target="_blank" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                Unduh Materi
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="md:col-span-2">
                        <?php if ($modul['file_laporan']): // Jika sudah mengumpulkan ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-semibold text-gray-700">Status Laporan</h5>
                                <p class="text-sm text-gray-500 mt-1">Anda telah mengumpulkan laporan.</p>
                                <a href="../uploads/laporan/<?php echo $modul['file_laporan']; ?>" target="_blank" class="text-blue-500 hover:underline text-sm">Lihat file</a>
                                
                                <?php if ($modul['nilai'] !== null): // Jika sudah dinilai ?>
                                    <div class="mt-4 border-t pt-4">
                                        <h6 class="font-semibold text-green-700">Sudah Dinilai</h6>
                                        <p class="text-2xl font-bold"><?php echo $modul['nilai']; ?></p>
                                        <?php if ($modul['feedback']): ?>
                                            <p class="text-sm text-gray-600 mt-2"><strong>Feedback:</strong> <?php echo htmlspecialchars($modul['feedback']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: // Jika belum dinilai ?>
                                    <p class="mt-4 text-sm font-semibold text-yellow-700">Menunggu penilaian dari asisten.</p>
                                <?php endif; ?>
                            </div>
                        <?php else: // Jika belum mengumpulkan ?>
                             <form action="course_detail.php?id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                                <label for="file_laporan_<?php echo $modul['id']; ?>" class="block text-sm font-medium text-gray-700">Kumpulkan Laporan (PDF/DOCX)</label>
                                <div class="mt-1 flex items-center space-x-2">
                                    <input type="file" name="file_laporan" id="file_laporan_<?php echo $modul['id']; ?>" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                    <button type="submit" name="submit_laporan" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md text-sm">Kumpul</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center p-8 bg-white rounded-lg shadow">
            <h3 class="text-xl text-gray-700">Belum ada modul untuk praktikum ini.</h3>
            <p class="text-gray-500 mt-2">Silakan hubungi asisten praktikum Anda.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt_course->close();
$stmt_modul->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>