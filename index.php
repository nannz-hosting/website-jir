<?php
require_once 'config/database.php';

// Ambil akun tersedia
$akun = $conn->query("SELECT * FROM akun_ff WHERE status = 'tersedia' ORDER BY created_at DESC");

// Proses pembelian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beli'])) {
    $akun_id = sanitize($_POST['akun_id']);
    $customer_name = sanitize($_POST['customer_name']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $customer_email = sanitize($_POST['customer_email']);
    $metode = sanitize($_POST['metode']);
    
    // Upload bukti
    $target_dir = "uploads/bukti/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_name = time() . '_' . basename($_FILES["bukti"]["name"]);
    $target_file = $target_dir . $file_name;
    move_uploaded_file($_FILES["bukti"]["tmp_name"], $target_file);
    
    $stmt = $conn->prepare("INSERT INTO transaksi (akun_id, customer_name, customer_phone, customer_email, metode_pembayaran, bukti_transfer) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $akun_id, $customer_name, $customer_phone, $customer_email, $metode, $file_name);
    
    if ($stmt->execute()) {
        $success = "Pembelian berhasil! Tunggu konfirmasi admin.";
    } else {
        $error = "Gagal melakukan pembelian!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FF Account Store - Jual Akun Free Fire Premium</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #1a0000, #2d0a0a);
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #c9a44e;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        .navbar-brand span { color: #c9a44e; }
        .navbar-brand i { color: #c9a44e; margin-right: 10px; }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Hero */
        .hero {
            text-align: center;
            padding: 60px 0;
            background: linear-gradient(135deg, rgba(201,164,78,0.1), transparent);
            border-radius: 20px;
            margin-bottom: 40px;
        }
        .hero h1 {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .hero h1 span { color: #c9a44e; }
        .hero p { color: #aaa; font-size: 18px; }
        
        /* Filter */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .filter-bar select, .filter-bar input {
            padding: 10px 20px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-bar select:focus, .filter-bar input:focus {
            border-color: #c9a44e;
            outline: none;
        }
        
        /* Account Grid */
        .account-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .account-card {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 25px;
            border: 1px solid #333;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .account-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #c9a44e, #a8832e);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .account-card:hover::before {
            transform: scaleX(1);
        }
        .account-card:hover {
            transform: translateY(-10px);
            border-color: #c9a44e;
            box-shadow: 0 10px 40px rgba(201, 164, 78, 0.15);
        }
        
        .account-card .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #00c853;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .account-card .badge.sold {
            background: #ff1744;
        }
        
        .account-card h3 {
            font-size: 22px;
            color: #c9a44e;
            margin-bottom: 10px;
        }
        .account-card .id-akun {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }
        .account-card .id-akun span {
            color: #c9a44e;
            font-weight: 600;
        }
        .account-card .spek {
            color: #ccc;
            margin: 15px 0;
            font-size: 14px;
            line-height: 1.6;
            min-height: 60px;
        }
        .account-card .spek i {
            color: #c9a44e;
            margin-right: 5px;
        }
        .account-card .harga {
            font-size: 24px;
            font-weight: 700;
            color: #c9a44e;
            margin: 15px 0;
        }
        .account-card .details {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #888;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .account-card .details span {
            background: #222;
            padding: 4px 12px;
            border-radius: 20px;
        }
        
        .btn-beli {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #c9a44e, #a8832e);
            border: none;
            border-radius: 10px;
            color: #1a0000;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-beli:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 20px rgba(201, 164, 78, 0.3);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: #1a1a1a;
            border-radius: 20px;
            padding: 35px;
            max-width: 550px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            border: 2px solid #c9a44e;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-content h2 {
            color: #c9a44e;
            margin-bottom: 20px;
        }
        .modal-content .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #222;
        }
        .modal-content .detail-item .label { color: #888; }
        .modal-content .detail-item .value { color: #fff; font-weight: 600; }
        
        .form-group-modal {
            margin: 15px 0;
        }
        .form-group-modal label {
            display: block;
            color: #c9a44e;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group-modal input, .form-group-modal select, .form-group-modal textarea {
            width: 100%;
            padding: 10px 15px;
            background: #2a2a2a;
            border: 2px solid #333;
            border-radius: 8px;
            color: #fff;
        }
        .form-group-modal input:focus, .form-group-modal select:focus, .form-group-modal textarea:focus {
            border-color: #c9a44e;
            outline: none;
        }
        .form-group-modal textarea { min-height: 80px; }
        
        .btn-close-modal {
            margin-top: 15px;
            padding: 10px;
            width: 100%;
            background: #ff1744;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-close-modal:hover { background: #d50000; }
        
        /* Payment Methods */
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .payment-option {
            padding: 10px;
            background: #222;
            border: 2px solid #333;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-option:hover, .payment-option.selected {
            border-color: #c9a44e;
            background: #2a1a0a;
        }
        .payment-option img, .payment-option i {
            font-size: 24px;
            color: #c9a44e;
            display: block;
            margin-bottom: 5px;
        }
        .payment-option span { font-size: 12px; color: #ccc; }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(0,255,0,0.1); border: 1px solid #00ff00; color: #00ff00; }
        .alert-danger { background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff0000; }
        
        .footer {
            text-align: center;
            padding: 30px 0;
            border-top: 1px solid #222;
            margin-top: 40px;
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .hero h1 { font-size: 32px; }
            .account-grid { grid-template-columns: 1fr; }
            .modal-content { padding: 20px; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-crown"></i> FF<span>Store</span>
        </div>
        <div>
            <a href="admin/login.php" style="color: #c9a44e; text-decoration: none; font-weight: 600;">
                <i class="fas fa-user-shield"></i> Admin
            </a>
        </div>
    </nav>
    
    <div class="container">
        <!-- Hero -->
        <div class="hero">
            <h1>🔥 Jual Beli Akun <span>Free Fire</span> Premium</h1>
            <p>Dapatkan akun FF terbaik dengan harga terjangkau dan proses cepat</p>
        </div>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Filter -->
        <div class="filter-bar">
            <select id="filterSort" onchange="sortAccounts()">
                <option value="terbaru">Terbaru</option>
                <option value="termurah">Termurah</option>
                <option value="termahal">Termahal</option>
            </select>
        </div>
        
        <!-- Account Grid -->
        <div class="account-grid" id="accountGrid">
            <?php while($row = $akun->fetch_assoc()): ?>
            <div class="account-card" data-harga="<?php echo $row['harga']; ?>" data-tanggal="<?php echo strtotime($row['created_at']); ?>">
                <div class="badge">Tersedia</div>
                <h3>Akun FF</h3>
                <div class="id-akun">
                    <i class="fas fa-id-card"></i> ID: <span><?php echo substr($row['id_akun'], 0, 3) . '****' . substr($row['id_akun'], -3); ?></span>
                </div>
                <div class="spek">
                    <i class="fas fa-star"></i> <?php echo $row['spek']; ?>
                </div>
                <div class="details">
                    <span><i class="fas fa-level-up-alt"></i> Level <?php echo $row['level']; ?></span>
                    <span><i class="fas fa-globe"></i> <?php echo $row['server']; ?></span>
                </div>
                <?php if ($row['detail_extra']): ?>
                <div class="spek" style="font-size:13px; color:#888;">
                    <i class="fas fa-gem"></i> <?php echo $row['detail_extra']; ?>
                </div>
                <?php endif; ?>
                <div class="harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                <button class="btn-beli" onclick="openModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['id_akun']); ?>', <?php echo $row['harga']; ?>)">
                    <i class="fas fa-shopping-cart"></i> Beli Sekarang
                </button>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Modal Pembelian -->
    <div class="modal" id="beliModal">
        <div class="modal-content">
            <h2><i class="fas fa-shopping-cart"></i> Detail Pembelian</h2>
            
            <div id="detailAkun">
                <div class="detail-item">
                    <span class="label">ID Akun</span>
                    <span class="value" id="modalIdAkun">-</span>
                </div>
                <div class="detail-item">
                    <span class="label">Harga</span>
                    <span class="value" id="modalHarga">-</span>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="formBeli">
                <input type="hidden" name="akun_id" id="modalAkunId">
                
                <div class="form-group-modal">
                    <label><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" name="customer_name" placeholder="Masukkan nama Anda" required>
                </div>
                
                <div class="form-group-modal">
                    <label><i class="fas fa-phone"></i> Nomor WhatsApp</label>
                    <input type="text" name="customer_phone" placeholder="08123456789" required>
                </div>
                
                <div class="form-group-modal">
                    <label><i class="fas fa-envelope"></i> Email (Opsional)</label>
                    <input type="email" name="customer_email" placeholder="email@example.com">
                </div>
                
                <div class="form-group-modal">
                    <label><i class="fas fa-credit-card"></i> Metode Pembayaran</label>
                    <div class="payment-grid">
                        <div class="payment-option" onclick="selectPayment(this, 'DANA')">
                            <i class="fas fa-wallet"></i>
                            <span>DANA</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'GoPay')">
                            <i class="fas fa-wallet"></i>
                            <span>GoPay</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'OVO')">
                            <i class="fas fa-wallet"></i>
                            <span>OVO</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'ShopeePay')">
                            <i class="fas fa-wallet"></i>
                            <span>ShopeePay</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'LinkAja')">
                            <i class="fas fa-wallet"></i>
                            <span>LinkAja</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'QRIS')">
                            <i class="fas fa-qrcode"></i>
                            <span>QRIS</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'Bank BCA')">
                            <i class="fas fa-university"></i>
                            <span>BCA</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'Bank BRI')">
                            <i class="fas fa-university"></i>
                            <span>BRI</span>
                        </div>
                        <div class="payment-option" onclick="selectPayment(this, 'Bank Mandiri')">
                            <i class="fas fa-university"></i>
                            <span>Mandiri</span>
                        </div>
                    </div>
                    <input type="hidden" name="metode" id="metodeInput" required>
                </div>
                
                <div class="form-group-modal">
                    <label><i class="fas fa-upload"></i> Upload Bukti Transfer</label>
                    <input type="file" name="bukti" accept="image/*" required>
                    <small style="color:#666; display:block; margin-top:5px;">Format: JPG, PNG, JPEG (Max 2MB)</small>
                </div>
                
                <button type="submit" name="beli" class="btn-beli" style="margin-top:15px;">
                    <i class="fas fa-check"></i> Konfirmasi Pembelian
                </button>
                <button type="button" class="btn-close-modal" onclick="closeModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </form>
        </div>
    </div>
    
    <div class="footer">
        <p>© 2026 FF Account Store - All Rights Reserved</p>
    </div>
    
    <script>
    function openModal(akunId, idAkun, harga) {
        document.getElementById('modalAkunId').value = akunId;
        document.getElementById('modalIdAkun').textContent = idAkun.substring(0, 3) + '****' + idAkun.substring(idAkun.length - 3);
        document.getElementById('modalHarga').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(harga);
        document.getElementById('beliModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        document.getElementById('beliModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    function selectPayment(element, metode) {
        document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('metodeInput').value = metode;
    }
    
    function sortAccounts() {
        const sort = document.getElementById('filterSort').value;
        const grid = document.getElementById('accountGrid');
        const cards = Array.from(grid.children