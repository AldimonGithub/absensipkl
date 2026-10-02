<?php
include '../config/database.php';
include '../includes/auth.php';
include '../includes/functions.php';

$auth = new Auth($conn);

// Check if not logged in
if (!$auth->isLoggedIn('admin')) {
    header('Location: login-admin.php');
    exit;
}

// Get all attendance data
$search = $_GET['search'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$query = "
    SELECT 
        s.id, s.nama, s.kelas, s.bidang, 
        am.tanggal, am.jam_masuk, am.status, am.status_waktu, am.keterangan, am.bukti_file,
        ak.jam_keluar, ak.status_pulang
    FROM siswa s
    LEFT JOIN absensi_masuk am ON s.id = am.siswa_id
    LEFT JOIN absensi_keluar ak ON s.id = ak.siswa_id AND am.tanggal = ak.tanggal
    WHERE 1=1
";

$params = [];
$types = '';

// Apply filters
if (!empty($search)) {
    $query .= " AND (s.nama LIKE ? OR s.nomor_hp LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($filter_status)) {
    $query .= " AND am.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($filter_date)) {
    $query .= " AND am.tanggal = ?";
    $params[] = $filter_date;
    $types .= 's';
}

$query .= " ORDER BY am.tanggal DESC, s.nama ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// Get total count for pagination
$count_query = "SELECT COUNT(DISTINCT CONCAT(am.siswa_id, '-', am.tanggal)) as total FROM absensi_masuk am WHERE 1=1";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Check if time >= 12:00 to show pulang section
$current_time = strtotime(date('H:i'));
$jam_12 = strtotime('12:00');
$show_pulang = $current_time >= $jam_12;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface - Sistem Absensi QR Code</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Dashboard Admin - Sistem Absensi QR Code</h1>
            <div class="admin-actions">
                <span>Selamat datang, Admin</span>
                <form method="GET" action="../index.php" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </header>
        
        <main class="admin-content">
            <!-- Filter Section -->
            <section class="filter-section">
                <h2>Filter & Cari</h2>
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="search">Cari Nama/HP</label>
                        <input type="text" id="search" name="search" placeholder="Masukkan nama atau nomor HP" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="filter_status">Status</label>
                        <select id="filter_status" name="filter_status">
                            <option value="">Semua Status</option>
                            <option value="Hadir" <?php echo $filter_status === 'Hadir' ? 'selected' : ''; ?>>Hadir</option>
                            <option value="Sakit" <?php echo $filter_status === 'Sakit' ? 'selected' : ''; ?>>Sakit</option>
                            <option value="Izin" <?php echo $filter_status === 'Izin' ? 'selected' : ''; ?>>Izin</option>
                            <option value="Absen" <?php echo $filter_status === 'Absen' ? 'selected' : ''; ?>>Absen</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="filter_date">Tanggal</label>
                        <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="admin-interface.php" class="btn btn-secondary">Reset</a>
                </form>
            </section>
            
            <!-- Data Table Section -->
            <section class="data-section">
                <h2>Data Absensi Siswa</h2>
                
                <?php if (empty($data)): ?>
                    <div class="alert alert-info">Tidak ada data absensi yang ditemukan</div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Bidang</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Status Masuk</th>
                                    <th>Status Waktu</th>
                                    <th>Keterangan</th>
                                    <th>Bukti File</th>
                                    <?php if ($show_pulang): ?>
                                        <th>Jam Pulang</th>
                                        <th>Status Pulang</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['bidang']); ?></td>
                                        <td><?php echo !empty($row['tanggal']) ? date('d/m/Y', strtotime($row['tanggal'])) : '-'; ?></td>
                                        <td><?php echo !empty($row['jam_masuk']) ? substr($row['jam_masuk'], 0, 5) : '-'; ?></td>
                                        <td>
                                            <?php if (!empty($row['status'])): ?>
                                                <span class="badge-status <?php echo strtolower($row['status']); ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['status_waktu'])): ?>
                                                <span class="badge-waktu <?php echo strtolower(str_replace(' ', '-', $row['status_waktu'])); ?>">
                                                    <?php echo htmlspecialchars($row['status_waktu']); ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo !empty($row['keterangan']) ? htmlspecialchars(substr($row['keterangan'], 0, 50)) . (strlen($row['keterangan']) > 50 ? '...' : '') : '-'; ?></td>
                                        <td>
                                            <?php if (!empty($row['bukti_file'])): ?>
                                                <a href="../uploads/<?php echo htmlspecialchars($row['bukti_file']); ?>" target="_blank" class="btn btn-sm btn-info">Lihat</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($show_pulang): ?>
                                            <td><?php echo !empty($row['jam_keluar']) ? substr($row['jam_keluar'], 0, 5) : '-'; ?></td>
                                            <td>
                                                <?php if (!empty($row['status_pulang'])): ?>
                                                    <span class="badge-pulang <?php echo strtolower(str_replace(' ', '-', $row['status_pulang'])); ?>">
                                                        <?php echo htmlspecialchars($row['status_pulang']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=1&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="btn btn-sm">Pertama</a>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="btn btn-sm">Sebelumnya</a>
                            <?php endif; ?>
                            
                            <span class="pagination-info">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="btn btn-sm">Berikutnya</a>
                                <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&filter_status=<?php echo urlencode($filter_status); ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="btn btn-sm">Terakhir</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
