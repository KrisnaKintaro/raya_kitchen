<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelanggan - Raya Kitchen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --rk-bg: #f8f7fa;
            --rk-lavender: #e8d8ec;
            --rk-lavender-soft: #f3eaf6;
            --rk-purple: #b98ac7;
            --rk-purple-dark: #a574b5;
            --rk-title: #c18ccb;
            --rk-text: #2a2430;
            --rk-muted: #6b5f73;
        }
        body {
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            background: var(--rk-bg);
            color: var(--rk-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex: 1; }

        /* Navbar */
        .rk-nav {
            background: var(--rk-lavender);
            box-shadow: 0 10px 30px rgba(185, 138, 199, .25);
            margin: 12px 16px 0;
            padding: 14px 40px;
            border-radius: 36px;
        }
        .rk-logo {
            width: 56px; height: 56px; background: #fff; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,.08); overflow: hidden;
        }
        .rk-logo img { width: 44px; height: 44px; object-fit: contain; }
        .rk-brand { font-weight: 900; font-size: 1.9rem; color: var(--rk-text); text-decoration: none; }
        .rk-pill-group {
            background: var(--rk-lavender-soft); border-radius: 50px; padding: 6px;
            display: inline-flex; gap: 6px; box-shadow: inset 0 0 0 2px #fff;
        }
        .rk-pill {
            padding: 10px 26px; border-radius: 50px; color: var(--rk-text);
            text-decoration: none; font-weight: 800;
        }
        .rk-pill.active {
            background: var(--rk-purple); color: #fff;
            box-shadow: 0 6px 14px rgba(185, 138, 199, .5);
        }
        .rk-link { color: var(--rk-muted); font-weight: 800; text-decoration: none; }
        .btn-rk {
            background: var(--rk-purple); color: #fff; border: 0; border-radius: 50px;
            padding: 12px 28px; font-weight: 800;
            box-shadow: 0 8px 18px rgba(185, 138, 199, .5);
            transition: .2s;
        }
        .btn-rk:hover { background: var(--rk-purple-dark); color: #fff; transform: translateY(-1px); }

        /* Judul & Kartu */
        .rk-title { font-weight: 900; color: var(--rk-title); font-size: 2.4rem; }
        .rk-sub { color: var(--rk-muted); font-weight: 600; }
        .rk-card {
            background: #fff; border: 0; border-radius: 28px;
            box-shadow: 0 12px 34px rgba(185, 138, 199, .18);
        }
        .rk-table { --bs-table-bg: transparent; margin: 0; }
        .rk-table thead th {
            background: var(--rk-lavender); color: var(--rk-text); font-weight: 800;
            border: 0; padding: 14px 16px;
        }
        .rk-table thead th:first-child { border-radius: 18px 0 0 18px; }
        .rk-table thead th:last-child { border-radius: 0 18px 18px 0; }
        .rk-table td { padding: 16px; border-color: #f0e8f3; font-weight: 600; }
        .rk-table tbody tr:hover { background: var(--rk-lavender-soft); }
        .badge-wa {
            background: var(--rk-lavender-soft); color: var(--rk-purple-dark);
            padding: 6px 14px; border-radius: 50px; font-weight: 800; text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="rk-nav d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rk-logo"><img src="/images/logo.png" alt="Logo" onerror="this.outerHTML='<i class=&quot;fa-solid fa-cookie-bite fa-lg&quot; style=&quot;color:#b98ac7&quot;></i>'"></div>
            <a href="#" class="rk-brand">Raya Kitchen</a>
        </div>

        <div class="rk-pill-group">
            <a href="#" class="rk-pill">Akses Admin</a>
            <a href="#" class="rk-pill active">Pelanggan</a>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="rk-link">Krisna Admin</span>
            <a href="#" class="btn-rk text-decoration-none">Keluar</a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="mb-4 px-md-4">
            <h2 class="rk-title mb-1">Daftar Pelanggan Terdaftar</h2>
            <p class="rk-sub mb-0">Memantau data pelanggan yang telah melakukan registrasi ke sistem Raya Kitchen.</p>
        </div>

        <div class="rk-card p-4 mx-md-4">
            <div class="table-responsive">
                <table class="table rk-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pelanggan</th>
                            <th>Email</th>
                            <th>No. Telepon / WA</th>
                            <th>Tanggal Registrasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="fw-bold">Siti Rahma</td>
                            <td>siti@gmail.com</td>
                            <td><a href="https://wa.me/6281234567890" class="badge-wa"><i class="fa-brands fa-whatsapp me-1"></i>081234567890</a></td>
                            <td>2026-06-01 10:30:00</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="fw-bold">Ahmad Fauzi</td>
                            <td>fauzi@yahoo.com</td>
                            <td><a href="https://wa.me/6289876543210" class="badge-wa"><i class="fa-brands fa-whatsapp me-1"></i>089876543210</a></td>
                            <td>2026-06-02 14:15:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>