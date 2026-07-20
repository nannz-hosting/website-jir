<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Ambil statistik
$total_akun = $conn->query("SELECT COUNT(*) as total FROM akun_ff")->fetch_assoc()['total'];
$akun_tersedia = $conn->query("SELECT COUNT(*) as total FROM akun_ff WHERE status = 'tersedia'")->fetch_assoc()['total'];
$akun_terjual = $conn->query("SELECT COUNT(*) as total FROM akun_ff WHERE status = 'terjual'")->fetch_assoc()['total'];
$pendapatan = $conn->query("SELECT SUM(harga) as total FROM akun_ff WHERE status = 'terjual'")->fetch_assoc()['total'] ?? 0;

// Ambil transaksi terbaru
$transaksi = $conn->query("
    SELECT t.*, a.id_akun, a.harga 
    FROM transaksi t 
    JOIN akun_ff a ON t.akun_id = a.id 
    ORDER BY t.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin FF Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
        }
        
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
        
        .sidebar-brand i {
            font-size: 40px;
            color: #c9a44e;
        }
        
        .sidebar-brand h2 {
            color: #fff;
            margin-top: 10px;
            font-size: 20px;
        }
        
        .sidebar-brand span {
            color: #c9a44e;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-menu li {
            margin-bottom: 5px;
        }
        
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
        
        .nav-menu li a:hover {
            background: rgba(201, 164, 78, 0.1);
            color: #c9a44e;
        }
        
        .nav-menu li a.active {
            background: rgba(201, 164, 78, 0.2);
            color: #c9a44e;
        }
        
        .nav-menu li a i {
            width: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #222;
            margin-bottom: 30px;
        }
        
        .top-bar h1 {
            font-size: 28px;
            color: #fff;
        }
        
        .top-bar h1 span {
            color: #c9a44e;
        }
        
        .top-bar .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .top-bar .admin-info span {
            color: #aaa;
        }
        
        .btn-logout {
            padding: 8px 20px;
            background: #ff0000;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #cc0000;
            transform: scale(1.05);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #c9a44e;
            box-shadow: 0 10px 30px rgba(201, 164, 78, 0.1);
        }
        
        .stat-card .stat-icon {
            font-size: 30px;
            color: #c9a44e;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 28px;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #888;
            font-size: 14px;
        }
        
        .stat-card .stat-change {
            display: inline-block;
            margin-top: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stat-change.positive {
            background: rgba(0, 255, 0, 0.1);
            color: #00ff00;
        }
        
        .stat-change.negative {
            background: rgba(255, 0, 0, 0.1);
            color: #ff0000;
        }
        
        .section-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .section-title span {
            color: #c9a44e;
        }
        
        .table-container {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #333;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #222;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            color: #c9a44e;
            font-weight: 600;
            font-size: 14px;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #222;
            color: #ccc;
        }
        
        table tbody tr:hover {
            background: #222;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.pending {
            background: rgba(255, 165, 0, 0.2);
            color: #ffa500;
        }
        
        .status-badge.verified {
            background: rgba(0, 255, 0, 0.2);
            color: #00ff00;
        }
        
        .status-badge.completed {
            background: rgba(0, 0, 255, 0.2);
            color: #00bfff;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 15px 10px;
            }
            
            .sidebar-brand h2,
            .sidebar-brand span,
            .nav-menu li a span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="akun.php"><i class="fas fa-gamepad"></i> <span>Kelola Akun</span></a></li>
            <li><a href="transaksi.php"><i class="fas fa-shopping-cart"></i> <span>Transaksi</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h1>Dashboard <span>Admin</span></h1>
            <div class="admin-info">
                <span><i class="fas fa-user"></i> <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-gamepad"></i></div>
                <h3><?php echo $total_akun; ?></h3>
                <p>Total Akun</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <h3><?php echo $akun_tersedia; ?></h3>
                <p>Akun Tersedia</p>
                <span class="stat-change positive">+12%</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <h3><?php echo $akun_terjual; ?></h3>
                <p>Akun Terjual</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <h3>Rp <?php echo number_format($pendapatan, 0, ',', '.'); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>
        
        <h3 class="section-title">Transaksi <span>Terbaru</span></h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Akun</th>
                        <th>Customer</th>
                        <th>Harga</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $transaksi->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['id_akun']; ?></td>
                        <td><?php echo $row['customer_name']; ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['metode_pembayaran']; ?></td>
                        <td><span class="status-badge <?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>