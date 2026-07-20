<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Proses CRUD
// Tambah akun
if (isset($_POST['tambah'])) {
    $id_akun = sanitize($_POST['id_akun']);
    $password_akun = sanitize($_POST['password_akun']);
    $spek = sanitize($_POST['spek']);
    $harga = sanitize($_POST['harga']);
    $level = sanitize($_POST['level']);
    $server = sanitize($_POST['server']);
    $status = sanitize($_POST['status']);
    $detail_extra = sanitize($_POST['detail_extra']);
    
    $stmt = $conn->prepare("INSERT INTO akun_ff (id_akun, password_akun, spek, harga, level, server, status, detail_extra) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdiss", $id_akun, $password_akun, $spek, $harga, $level, $server, $status, $detail_extra);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Akun berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan akun!";
    }
    $stmt->close();
    header('Location: akun.php');
    exit();
}

// Edit akun
if (isset($_POST['edit'])) {
    $id = sanitize($_POST['id']);
    $id_akun = sanitize($_POST['id_akun']);
    $password_akun = sanitize($_POST['password_akun']);
    $spek = sanitize($_POST['spek']);
    $harga = sanitize($_POST['harga']);
    $level = sanitize($_POST['level']);
    $server = sanitize($_POST['server']);
    $status = sanitize($_POST['status']);
    $detail_extra = sanitize($_POST['detail_extra']);
    
    $stmt = $conn->prepare("UPDATE akun_ff SET id_akun=?, password_akun=?, spek=?, harga=?, level=?, server=?, status=?, detail_extra=? WHERE id=?");
    $stmt->bind_param("sssdissi", $id_akun, $password_akun, $spek, $harga, $level, $server, $status, $detail_extra, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Akun berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal mengupdate akun!";
    }
    $stmt->close();
    header('Location: akun.php');
    exit();
}

// Hapus akun
if (isset($_GET['hapus'])) {
    $id = sanitize($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM akun_ff WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Akun berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus akun!";
    }
    $stmt->close();
    header('Location: akun.php');
    exit();
}

// Ambil data akun
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$filter_harga = isset($_GET['harga']) ? sanitize($_GET['harga']) : '';

$sql = "SELECT * FROM akun_ff WHERE 1=1";
if ($filter_status) {
    $sql .= " AND status = '$filter_status'";
}
if ($filter_harga == 'termurah') {
    $sql .= " ORDER BY harga ASC";
} elseif ($filter_harga == 'termahal') {
    $sql .= " ORDER BY harga DESC";
} else {
    $sql .= " ORDER BY created_at DESC";
}

$akun = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - Admin FF Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Sama dengan style dashboard */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0a0a0a; color: #fff; }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #1a0000, #2d0a0a);
            border-right: 2px solid #c9a44e;
            padding: 30px 20px;
            overflow-y: auto;
        }
        .sidebar-brand {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid #333;
            margin-bottom: 30px;
        }
        .sidebar-brand i { font-size: 40px; color: #c9a44e; }
        .sidebar-brand h2 { color: #fff; margin-top: 10px; font-size: 20px; }
        .sidebar-brand span { color: #c9a44e; }
        .nav-menu { list-style: none; }
        .nav-menu li { margin-bottom: 5px; }
        .nav-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #aaa;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            gap: 12px;
        }
        .nav-menu li a:hover { background: rgba(201, 164, 78, 0.1); color: #c9a44e; }
        .nav-menu li a.active { background: rgba(201, 164, 78, 0.2); color: #c9a44e; }
        .nav-menu li a i { width: 20px; }
        .main-content { margin-left: 250px; padding: 30px; }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #222;
            margin-bottom: 30px;
        }
        .top-bar h1 { font-size: 28px; color: #fff; }
        .top-bar h1 span { color: #c9a44e; }
        .top-bar .admin-info { display: flex; align-items: center; gap: 15px; }
        .top-bar .admin-info span { color: #aaa; }
        .btn-logout {
            padding: 8px 20px;
            background: #ff0000;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-logout:hover { background: #cc0000; transform: scale(1.05); }
        
        .btn-primary, .btn-success, .btn-danger, .btn-warning {
            padding: 8px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #c9a44e; color: #1a0000; }
        .btn-primary:hover { transform: scale(1.05); box-shadow: 0 5px 20px rgba(201, 164, 78, 0.3); }
        .btn-success { background: #00c853; color: #fff; }
        .btn-danger { background: #ff1744; color: #fff; }
        .btn-warning { background: #ff9100; color: #fff; }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        
        .form-container {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #333;
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; color: #c9a44e; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            background: #2a2a2a;
            border: 2px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #c9a44e;
            box-shadow: 0 0 20px rgba(201, 164, 78, 0.1);
        }
        .form-group textarea { min-height: 80px; resize: vertical; }
        
        .table-container {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #333;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        table thead { background: #222; }
        table th { padding: 15px; text-align: left; color: #c9a44e; font-weight: 600; font-size: 14px; }
        table td { padding: 15px; border-bottom: 1px solid #222; color: #ccc; }
        table tbody tr:hover { background: #222; }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.tersedia { background: rgba(0, 255, 0, 0.2); color: #00ff00; }
        .status-badge.terjual { background: rgba(255, 0, 0, 0.2); color: #ff0000; }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(0, 255, 0, 0.1); border: 1px solid #00ff00; color: #00ff00; }
        .alert-danger { background: rgba(255, 0, 0, 0.1); border: 1px solid #ff0000; color: #ff0000; }
        
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-bar select, .filter-bar input {
            padding: 8px 15px;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 15px 10px; }
            .sidebar-brand h2, .sidebar-brand span, .nav-menu li a span { display: none; }
            .main-content { margin-left: 70px; padding: 15px; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-crown"></i>
            <h2>FF<span>Store</span></h2>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="akun.php" class="active"><i class="fas fa-gamepad"></i> <span>Kelola Akun</span></a></li>
            <li><a href="transaksi.php"><i class="fas fa-shopping-cart"></i> <span>Transaksi</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Kelola <span>Akun</span></h1>
            <div class="admin-info">
                <span><i class="fas fa-user"></i> <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Form Tambah Akun -->
        <div class="form-container">
            <h3 style="color: #c9a44e; margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> Tambah Akun Baru</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>ID Akun</label>
                        <input type="text" name="id_akun" placeholder="Contoh: 123456789" required>
                    </div>
                    <div class="form-group">
                        <label>Password Akun</label>
                        <input type="text" name="password_akun" placeholder="Password akun" required>
                    </div>
                    <div class="form-group">
                        <label>Spek Akun</label>
                        <textarea name="spek" placeholder="Jumlah skin, senjata, karakter, pet, dll" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" placeholder="100000" required>
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="number" name="level" placeholder="50" required>
                    </div>
                    <div class="form-group">
                        <label>Server</label>
                        <input type="text" name="server" placeholder="Contoh: Asia" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="terjual">Terjual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Detail Tambahan</label>
                        <textarea name="detail_extra" placeholder="Jumlah diamond, battle pass, dll"></textarea>
                    </div>
                </div>
                <button type="submit" name="tambah" class="btn-success" style="padding: 12px 30px; font-size: 16px;">
                    <i class="fas fa-save"></i> Simpan Akun
                </button>
            </form>
        </div>
        
        <!-- Filter -->
        <div class="filter-bar">
            <select onchange="window.location.href='?status='+this.value+'&harga=<?php echo $filter_harga; ?>'">
                <option value="">Semua Status</option>
                <option value="tersedia" <?php echo $filter_status == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                <option value="terjual" <?php echo $filter_status == 'terjual' ? 'selected' : ''; ?>>Terjual</option>
            </select>
            <select onchange="window.location.href='?status=<?php echo $filter_status; ?>&harga='+this.value">
                <option value="">Urutkan</option>
                <option value="termurah" <?php echo $filter_harga == 'termurah' ? 'selected' : ''; ?>>Termurah</option>
                <option value="termahal" <?php echo $filter_harga == 'termahal' ? 'selected' : ''; ?>>Termahal</option>
            </select>
        </div>
        
        <!-- Daftar Akun -->
        <div class="table-container">
            <h3 style="color: #c9a44e; margin-bottom: 20px;"><i class="fas fa-list"></i> Daftar Akun</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID Akun</th>
                        <th>Spek</th>
                        <th>Harga</th>
                        <th>Level</th>
                        <th>Server</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $akun->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['id_akun']; ?></td>
                        <td><?php echo substr($row['spek'], 0, 30) . '...'; ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['level']; ?></td>
                        <td><?php echo $row['server']; ?></td>
                        <td><span class="status-badge <?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td>
                            <button class="btn-warning btn-sm" onclick="editAkun(<?php echo $row['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?hapus=<?php echo $row['id']; ?>" class="btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Edit -->
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#1a1a1a; padding:30px; border-radius:15px; max-width:600px; width:90%; max-height:80vh; overflow-y:auto; border:2px solid #c9a44e;">
            <h3 style="color:#c9a44e; margin-bottom:20px;"><i class="fas fa-edit"></i> Edit Akun</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label>ID Akun</label>
                        <input type="text" name="id_akun" id="edit_id_akun" required>
                    </div>
                    <div class="form-group">
                        <label>Password Akun</label>
                        <input type="text" name="password_akun" id="edit_password_akun" required>
                    </div>
                    <div class="form-group">
                        <label>Spek Akun</label>
                        <textarea name="spek" id="edit_spek" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" id="edit_harga" required>
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="number" name="level" id="edit_level" required>
                    </div>
                    <div class="form-group">
                        <label>Server</label>
                        <input type="text" name="server" id="edit_server" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit_status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="terjual">Terjual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Detail Tambahan</label>
                        <textarea name="detail_extra" id="edit_detail_extra"></textarea>
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" name="edit" class="btn-success" style="padding:12px 30px;">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <button type="button" onclick="closeEdit()" class="btn-danger" style="padding:12px 30px;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function editAkun(id) {
        // Ambil data dari row yang dipilih
        fetch(`get_akun.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_id_akun').value = data.id_akun;
                document.getElementById('edit_password_akun').value = data.password_akun;
                document.getElementById('edit_spek').value = data.spek;
                document.getElementById('edit_harga').value = data.harga;
                document.getElementById('edit_level').value = data.level