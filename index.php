<?php
session_start();

// ==========================================================
// Inisialisasi struktur data tugas (array asosiatif)
// Disimpan di $_SESSION agar data tetap ada selama sesi
// browser masih berjalan (tanpa perlu database).
// ==========================================================
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [
        ["id" => 1, "title" => "Belajar PHP",       "status" => "belum"],
        ["id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai"],
    ];
}

// ==========================================================
// FUNCTION: tambahTugas()
// Menambahkan tugas baru ke dalam array tasks.
// ==========================================================
function tambahTugas($judulTugas)
{
    $judulTugas = trim($judulTugas);
    if ($judulTugas === "") {
        return; // Abaikan input kosong
    }

    // id baru = id terbesar yang ada + 1, supaya tetap unik
    // walaupun sudah ada tugas yang dihapus sebelumnya.
    $idBaru = 1;
    foreach ($_SESSION['tasks'] as $tugas) {
        if ($tugas['id'] >= $idBaru) {
            $idBaru = $tugas['id'] + 1;
        }
    }

    $_SESSION['tasks'][] = [
        "id"     => $idBaru,
        "title"  => htmlspecialchars($judulTugas),
        "status" => "belum",
    ];
}

// ==========================================================
// FUNCTION: ubahStatusTugas()
// Mengubah status sebuah tugas: belum <-> selesai
// (dipakai oleh checkbox pada tampilan).
// ==========================================================
function ubahStatusTugas($id)
{
    foreach ($_SESSION['tasks'] as $index => $tugas) {
        if ($tugas['id'] == $id) {
            $_SESSION['tasks'][$index]['status'] =
                ($tugas['status'] === 'selesai') ? 'belum' : 'selesai';
            break;
        }
    }
}

// ==========================================================
// FUNCTION: hapusTugas()
// Menghapus satu tugas berdasarkan id, lalu merapikan
// ulang index array dengan array_values().
// ==========================================================
function hapusTugas($id)
{
    foreach ($_SESSION['tasks'] as $index => $tugas) {
        if ($tugas['id'] == $id) {
            unset($_SESSION['tasks'][$index]);
            break;
        }
    }
    $_SESSION['tasks'] = array_values($_SESSION['tasks']);
}

// ==========================================================
// Proses Action (tambah / toggle / hapus) — dijalankan
// SEBELUM HTML dirender, memakai pola Post/Redirect/Get
// supaya form tidak ter-submit ulang saat halaman di-refresh.
// ==========================================================

// Aksi tambah tugas (form method="post")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    tambahTugas($_POST['judul_tugas'] ?? '');
    header("Location: index.php");
    exit;
}

// Aksi ubah status (checkbox, dikirim lewat ?toggle=id)
if (isset($_GET['toggle'])) {
    ubahStatusTugas((int) $_GET['toggle']);
    header("Location: index.php");
    exit;
}

// Aksi hapus tugas (tombol Hapus, dikirim lewat ?hapus=id)
if (isset($_GET['hapus'])) {
    hapusTugas((int) $_GET['hapus']);
    header("Location: index.php");
    exit;
}

// ==========================================================
// FUNCTION: tampilkanDaftar()
// Menampilkan seluruh daftar tugas dalam bentuk baris tabel.
// Dipisah dari logika utama agar kode lebih terstruktur
// dan reusable.
// ==========================================================
function tampilkanDaftar($daftarTugas)
{
    if (empty($daftarTugas)) {
        echo '<tr><td colspan="3" class="baris-kosong">Belum ada tugas.</td></tr>';
        return;
    }

    foreach ($daftarTugas as $tugas) {
        $sudahSelesai = ($tugas['status'] === 'selesai');
        $labelStatus  = $sudahSelesai ? 'Selesai' : 'Belum';
        $kelasBadge   = $sudahSelesai ? 'badge-selesai' : 'badge-belum';
        $kelasBaris   = $sudahSelesai ? 'baris-selesai' : '';

        echo '<tr class="' . $kelasBaris . '">';

        // Kolom checkbox (tandai selesai)
        echo '<td class="kolom-checkbox">';
        echo   '<input type="checkbox" '
              . 'onchange="location.href=\'index.php?toggle=' . (int) $tugas['id'] . '\'" '
              . ($sudahSelesai ? 'checked' : '') . '>';
        echo '</td>';

        // Kolom judul tugas + badge status
        echo '<td class="kolom-judul">';
        echo   '<span>' . htmlspecialchars($tugas['title']) . '</span>';
        echo   '<span class="badge-status ' . $kelasBadge . '">' . $labelStatus . '</span>';
        echo '</td>';

        // Kolom aksi (hapus)
        echo '<td class="kolom-aksi">';
        echo   '<a href="index.php?hapus=' . (int) $tugas['id'] . '" '
              . 'class="tombol-hapus" '
              . 'onclick="return confirm(\'Hapus tugas ini?\')">Hapus</a>';
        echo '</td>';

        echo '</tr>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="halaman">

    <section class="kartu">

        <header class="banner">
            <h1>Aplikasi To-Do List</h1>
        </header>

        <div class="isi-kartu">

            <!-- Form input judul tugas baru -->
            <form action="index.php" method="post" class="form-tambah">
                <input type="hidden" name="aksi" value="tambah">
                <input type="text" name="judul_tugas" class="input-tugas"
                       placeholder="Tulis tugas baru..." required autofocus>
                <button type="submit" class="tombol-tambah">Tambah</button>
            </form>

            <!-- Daftar tugas -->
            <table class="tabel-tugas">
                <tbody>
                    <?php tampilkanDaftar($_SESSION['tasks']); ?>
                </tbody>
            </table>

        </div>

    </section>

</main>
</body>
</html>