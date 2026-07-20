<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Verifikasi transaksi
if (isset($_GET['verify'])) {
    $id = sanitize($_GET['verify']);
    $conn->query("UPDATE transaksi SET status = 'verified' WHERE id = $id");
    header('Location: transaksi.php');
    exit();
}

// Complete transaksi (kirim akun ke customer)
if (isset($_GET['complete'])) {
    $id = sanitize($_GET['complete']);
    $trans = $conn->query("SELECT akun_id FROM transaksi WHERE id = $id")->fetch_assoc();
    $conn->query("UPDATE transaksi SET status = 'completed' WHERE id = $id");
    $conn->query("UPDATE akun_ff SET status = 'terjual' WHERE id = " . $trans['akun_id']);
    header('Location: transaksi.php');
    exit();
}

$transaksi = $conn->query("
    SELECT t.*, a.id_akun, a.harga 
    FROM transaksi t 
    JOIN akun_ff a ON t.akun_id = a.id 
    ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Admin FF Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Styling sama seperti halaman admin lainnya */
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
        .sidebar-brand { text-align: center; padding-bottom: 30px; border-bottom: 1px solid #333; margin-bottom: 30px; }
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
        
        .btn-success, .btn-warning, .btn-danger {
            padding: 6px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-success { background: #00c853; color: #fff; }
        .btn-warning { background: #ff9100; color: #fff; }
        .btn-danger { background: #ff1744; color: #fff; }
        .btn-sm { padding: 5px 12px; font-size: 11px; }
        
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
        .status-badge.pending { background: rgba(255, 165, 0, 0.2); color: #ffa500; }
        .status-badge.verified { background: rgba(0, 255, 0, 0.2); color: #00ff00; }
        .status-badge.completed { background: rgba(0, 0, 255, 0.2); color: #00bfff; }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 15px 10px; }
            .sidebar-brand h2, .sidebar-brand span, .nav-menu li a span { display: none; }
            .main-content { margin-left: 70px; padding: 15px; }
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
            <li><a href="akun.php"><i class="fas fa-gamepad"></i> <span>Kelola Akun</span></a></li>
            <li><a href="transaksi.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Transaksi</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Manajemen <span>Transaksi</span></h1>
            <div class="admin-info">
                <span><i class="fas fa-user"></i> <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Akun</th>
                        <th>Customer</th>
                        <th>HP</th>
                        <th>Harga</th>
                        <th>Metode</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $transaksi->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['id_akun']; ?></td>
                        <td><?php echo $row['customer_name']; ?></td>
                        <td><?php echo $row['customer_phone']; ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['metode_pembayaran']; ?></td>
                        <td>
                            <?php if ($row['bukti_transfer']): ?>
                            <a href="../uploads/bukti/<?php echo $row['bukti_transfer']; ?>" target="_blank" style="color: #c9a44e;">
                                <i class="fas fa-image"></i> Lihat
                            </a>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge <?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                            <a href="?verify=<?php echo $row['id']; ?>" class="btn-success btn-sm">
                                <i class="fas fa-check"></i> Verifikasi
                            </a>
                            <?php endif; ?>
                            <?php if ($row['status'] == 'verified'): ?>
                            <a href="?complete=<?php echo $row['id']; ?>" class="btn-warning btn-sm">
                                <i class="fas fa-send"></i> Kirim Akun
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>