<?php
session_start();


// Inisialisasi struktur data tugas
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [
        ["id" => 1, "title" => "Belajar PHP",       "status" => "belum"],
        ["id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai"],
    ];
}


// FUNCTION: tambahTugas()
function tambahTugas($judulTugas)
{
    $judulTugas = trim($judulTugas);

    if ($judulTugas === "") {
        return;
    }

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


// FUNCTION: ubahStatusTugas()
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


// FUNCTION: hapusTugas()
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


// FUNCTION: editTugas()
function editTugas($id, $judulBaru)
{
    $judulBaru = trim($judulBaru);

    if ($judulBaru === "") {
        return;
    }

    foreach ($_SESSION['tasks'] as $index => $tugas) {
        if ($tugas['id'] == $id) {
            $_SESSION['tasks'][$index]['title'] =
                htmlspecialchars($judulBaru);

            break;
        }
    }
}


// Proses tambah tugas
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'tambah'
) {
    tambahTugas($_POST['judul_tugas'] ?? '');

    header("Location: index.php");
    exit;
}


// Proses edit tugas
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'edit'
) {
    editTugas(
        (int) ($_POST['id'] ?? 0),
        $_POST['judul_edit'] ?? ''
    );

    header("Location: index.php");
    exit;
}


// Aksi ubah status
if (isset($_GET['toggle'])) {
    ubahStatusTugas((int) $_GET['toggle']);

    header("Location: index.php");
    exit;
}


// Aksi hapus tugas
if (isset($_GET['hapus'])) {
    hapusTugas((int) $_GET['hapus']);

    header("Location: index.php");
    exit;
}


// FUNCTION: tampilkanDaftar()
function tampilkanDaftar($daftarTugas)
{
    if (empty($daftarTugas)) {
        echo '<tr>
                <td colspan="3" class="baris-kosong">
                    Belum ada tugas.
                </td>
              </tr>';

        return;
    }

    foreach ($daftarTugas as $tugas) {

        $sudahSelesai = ($tugas['status'] === 'selesai');

        $labelStatus = $sudahSelesai
            ? 'Selesai'
            : 'Belum';

        $kelasBadge = $sudahSelesai
            ? 'badge-selesai'
            : 'badge-belum';

        $kelasBaris = $sudahSelesai
            ? 'baris-selesai'
            : '';

        echo '<tr class="' . $kelasBaris . '">';


        // CHECKBOX
        echo '<td class="kolom-checkbox">';

        echo '<input type="checkbox"
                onchange="location.href=\'index.php?toggle='
                . (int) $tugas['id']
                . '\'"
                ' . ($sudahSelesai ? 'checked' : '') . '>';

        echo '</td>';


        // JUDUL + STATUS
        echo '<td class="kolom-judul">';

        echo '<span>'
            . htmlspecialchars($tugas['title'])
            . '</span>';

        echo '<span class="badge-status '
            . $kelasBadge
            . '">'
            . $labelStatus
            . '</span>';

        echo '</td>';


        // AKSI
        echo '<td class="kolom-aksi">';

        // Tombol EDIT
        echo '<button type="button"
                class="tombol-edit"
                onclick="toggleEdit(' . (int) $tugas['id'] . ')">
                Edit
              </button>';

        // Tombol HAPUS
        echo '<a href="index.php?hapus='
            . (int) $tugas['id']
            . '"
            class="tombol-hapus"
            onclick="return confirm(\'Hapus tugas ini?\')">
            Hapus
            </a>';

        echo '</td>';

        echo '</tr>';


        // FORM EDIT
        echo '<tr id="edit-' . (int) $tugas['id'] . '"
                class="baris-edit">';

        echo '<td colspan="3">';

        echo '<form action="index.php"
                method="post"
                class="form-edit">';

        echo '<input type="hidden"
                name="aksi"
                value="edit">';

        echo '<input type="hidden"
                name="id"
                value="' . (int) $tugas['id'] . '">';

        echo '<input type="text"
                name="judul_edit"
                value="' . htmlspecialchars($tugas['title']) . '"
                class="input-edit"
                required>';

        echo '<button type="submit"
                class="tombol-simpan">
                Simpan
              </button>';

        echo '</form>';

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

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="assets/style.css">

</head>


<body>

<main class="halaman">

    <section class="kartu">

        <header class="banner">

            <h1>Aplikasi To-Do List</h1>

        </header>


        <div class="isi-kartu">


            <!-- FORM TAMBAH -->
            <form action="index.php"
                  method="post"
                  class="form-tambah">

                <input type="hidden"
                       name="aksi"
                       value="tambah">

                <input type="text"
                       name="judul_tugas"
                       class="input-tugas"
                       placeholder="Tulis tugas baru..."
                       required
                       autofocus>

                <button type="submit"
                        class="tombol-tambah">
                    Tambah
                </button>

            </form>


            <!-- DAFTAR TUGAS -->
            <table class="tabel-tugas">

                <tbody>

                    <?php
                    tampilkanDaftar($_SESSION['tasks']);
                    ?>

                </tbody>

            </table>


        </div>

    </section>

</main>


<script>
function toggleEdit(id) {
    const barisEdit = document.getElementById('edit-' + id);

    if (!barisEdit) {
        return;
    }

    // Tutup form edit lain yang sedang terbuka
    document.querySelectorAll('.baris-edit.terbuka').forEach(function (baris) {
        if (baris !== barisEdit) {
            baris.classList.remove('terbuka');
        }
    });

    barisEdit.classList.toggle('terbuka');

    // Fokus ke input saat form dibuka
    if (barisEdit.classList.contains('terbuka')) {
        const input = barisEdit.querySelector('.input-edit');

        if (input) {
            input.focus();
            input.select();
        }
    }
}
</script>

</body>

</html>
