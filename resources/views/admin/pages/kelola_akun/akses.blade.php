<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akses Admin & Staf - Raya Kitchen</title>
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
            border-radius: 0 0 36px 36px;
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

        /* Tombol */
        .btn-rk {
            background: var(--rk-purple); color: #fff; border: 0; border-radius: 50px;
            padding: 12px 28px; font-weight: 800;
            box-shadow: 0 8px 18px rgba(185, 138, 199, .5);
            transition: .2s;
        }
        .btn-rk:hover { background: var(--rk-purple-dark); color: #fff; transform: translateY(-1px); }
        .btn-icon {
            width: 38px; height: 38px; border-radius: 50%; border: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--rk-lavender-soft); color: var(--rk-purple-dark); transition: .2s;
        }
        .btn-icon:hover { background: var(--rk-lavender); }
        .btn-icon.danger { background: #fde8ec; color: #d9534f; }
        .btn-icon.danger:hover { background: #f9d0d8; }

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
        .badge-rk { padding: 8px 16px; border-radius: 50px; font-weight: 800; }
        .badge-super { background: #fde8ec; color: #d9534f; }
        .badge-staf { background: var(--rk-lavender); color: var(--rk-purple-dark); }

        /* Modal */
        .modal-content { border: 0; border-radius: 28px; }
        .form-control, .form-select {
            border-radius: 50px; border: 2px solid var(--rk-lavender); padding: 10px 18px; font-weight: 600;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--rk-purple); box-shadow: 0 0 0 .2rem rgba(185,138,199,.25);
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
            <a href="#" class="rk-pill active">Akses Admin</a>
            <a href="#" class="rk-pill">Pelanggan</a>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="rk-link">Krisna Admin</span>
            <a href="#" class="btn-rk text-decoration-none">Keluar</a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 px-md-4">
            <div>
                <h2 class="rk-title mb-1">Kelola Akses (Admin &amp; Staf)</h2>
                <p class="rk-sub mb-0">Manajemen hak akses akun pengurus dapur Raya Kitchen.</p>
            </div>
            <button class="btn-rk" data-bs-toggle="modal" data-bs-target="#modalTambahAkun">
                <i class="fa-solid fa-user-plus me-2"></i>Tambah Akun Baru
            </button>
        </div>

        <div class="rk-card p-4 mx-md-4">
            <div class="table-responsive">
                <table class="table rk-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role / Akses</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <div class="fw-bold">Krisna Admin</div>
                                <small class="rk-sub">ID: #ADM-001</small>
                            </td>
                            <td>krisna@rayakitchen.com</td>
                            <td><span class="badge-rk badge-super">Super Admin</span></td>
                            <td class="text-center">
                                <button class="btn-icon me-1" title="Reset Password"><i class="fa-solid fa-key"></i></button>
                                <button class="btn-icon danger" title="Hapus Akun"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>
                                <div class="fw-bold">Budi Staf Dapur</div>
                                <small class="rk-sub">ID: #STF-002</small>
                            </td>
                            <td>budi@rayakitchen.com</td>
                            <td><span class="badge-rk badge-staf">Staf Operasional</span></td>
                            <td class="text-center">
                                <button class="btn-icon me-1" title="Reset Password"><i class="fa-solid fa-key"></i></button>
                                <button class="btn-icon danger" title="Hapus Akun"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Akun -->
    <div class="modal fade" id="modalTambahAkun" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title rk-title fs-3">Tambah Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="mb-3"><input type="text" class="form-control" placeholder="Nama lengkap"></div>
                        <div class="mb-3"><input type="email" class="form-control" placeholder="Email"></div>
                        <div class="mb-3"><input type="password" class="form-control" placeholder="Password"></div>
                        <select class="form-select">
                            <option>Staf Operasional</option>
                            <option>Super Admin</option>
                        </select>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn-icon px-4 w-auto" style="border-radius:50px;height:46px" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-rk">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>