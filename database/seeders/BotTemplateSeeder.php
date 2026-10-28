<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BotTemplate;
use Illuminate\Support\Facades\DB;

class BotTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongin tabel dulu biar datanya nggak dobel kalau di-seed ulang
        BotTemplate::truncate();

        $templates = [
            [
                'session_state' => 'sapaan',
                'description' => 'Pesan sapaan awal saat pelanggan baru chat atau kembali ke menu utama',
                'is_active' => true,
                'message_text' => "Halo Kak {nama}! Selamat datang di Raya Kitchen. 🍞✨\n\nKetik angka dari menu di bawah ini untuk mulai:\n1. 📖 Lihat Katalog Produk\n2. 🛒 Cek Keranjang Saya\n3. 📦 Cek Status Pesanan\n4. ℹ️ Cara Pemesanan"
            ],
            [
                'session_state' => 'instruksi',
                'description' => 'Pesan panduan cara menggunakan bot',
                'is_active' => true,
                'message_text' => "Cara pesannya gampang banget Kak!\n\n1. Pilih 'Lihat Katalog' lalu ketik angka produk yang mau dibeli.\n2. Masukkan jumlah produk.\n3. Ikuti langkah pengisian data pengiriman.\n4. Pilih metode pembayaran (Lunas, DP, atau COD).\n5. Lakukan pembayaran via QRIS dan pesanan otomatis diproses!\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'lihat_kategori',
                'description' => 'Menampilkan daftar kategori produk yang aktif',
                'is_active' => true,
                'message_text' => "Silakan pilih kategori produk yang mau dilihat:\n\n{daftar_kategori}\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'lihat_varian',
                'description' => 'Menampilkan daftar varian dari kategori yang dipilih',
                'is_active' => true,
                'message_text' => "Kategori: *{nama_kategori}*\nPilih varian yang mau dilihat detailnya:\n\n{daftar_varian}\n0. 🔙 Kembali ke Daftar Kategori"
            ],
            [
                'session_state' => 'detail_varian',
                'description' => 'Menampilkan detail produk dan opsi tambah ke keranjang',
                'is_active' => true,
                'message_text' => "Detail Produk:\n\n🍞 *{nama_produk}*\n🏷️ Kategori: {kategori_produk}\n💰 Harga: Rp {harga_produk}\n\n📝 Deskripsi:\n{deskripsi_produk}\n\nKetik angka berikut:\n1. 🛒 Tambah ke Keranjang\n0. 🔙 Kembali ke Daftar Varian"
            ],
            [
                'session_state' => 'input_qty',
                'description' => 'Meminta jumlah produk yang ingin dimasukkan ke keranjang',
                'is_active' => true,
                'message_text' => "Berapa banyak *{nama_produk}* yang mau dipesan? (Ketik angkanya saja, misal: 2)\n\n0. 🔙 Batal & Kembali"
            ],
            [
                'session_state' => 'keranjang',
                'description' => 'Menampilkan isi keranjang belanja dan opsi checkout',
                'is_active' => true,
                'message_text' => "🛒 *Isi Keranjang Kamu:*\n\n{isi_keranjang}\nTotal Belanja: Rp {total_harga}\n\nPilih aksi:\n1. ✅ Lanjut Checkout\n2. ➕ Tambah Produk Lain\n3. 🗑️ Kosongkan Keranjang\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'checkout_nama',
                'description' => 'Meminta nama penerima saat checkout',
                'is_active' => true,
                'message_text' => "Mulai proses checkout ya!\n\nAtas nama siapa pesanan ini? (Ketik nama penerima)\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_alamat',
                'description' => 'Meminta alamat pengiriman atau memilih dari riwayat',
                'is_active' => true,
                'message_text' => "Kirim ke alamat mana Kak?\n\n{daftar_alamat}\nKetik angka untuk memilih alamat di atas, atau ketik langsung alamat lengkap baru (Jalan, RT/RW, Kecamatan, Kota, Patokan).\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_tanggal',
                'description' => 'Meminta tanggal pengiriman/pengambilan',
                'is_active' => true,
                'message_text' => "Kapan pesanan mau dikirim/diambil? \n(Ketik dengan format YYYY-MM-DD, contoh: 2026-11-25)\n*Minimal pemesanan H-1*\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_jam',
                'description' => 'Meminta jam pengiriman/pengambilan',
                'is_active' => true,
                'message_text' => "Jam berapa Kak?\n(Pilih antara 08:00 sampai 17:00, contoh: 14:00)\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_metode_bayar',
                'description' => 'Memilih skema pembayaran',
                'is_active' => true,
                'message_text' => "Pilih skema pembayaran:\n\n1. 💳 Bayar Lunas (100% via QRIS)\n2. ⏳ DP Dulu (30% via QRIS, sisa via sistem)\n3. 🤝 COD (Bayar di tempat, wajib transfer DP 30%)\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_konfirmasi',
                'description' => 'Menampilkan ringkasan final sebelum generate QRIS',
                'is_active' => true,
                'message_text' => "🧾 *Ringkasan Pesanan*\n{ringkasan_pesanan}\n\n*Total Tagihan (Sesuai Skema): Rp {total_tagihan}*\n\nApakah data pesanan sudah benar?\n1. ✅ Ya, Buat Pesanan & Bayar\n2. ❌ Tidak, Batal Checkout"
            ],
            [
                'session_state' => 'pembayaran_qris',
                'description' => 'Menampilkan pesan instruksi pembayaran beserta gambar QRIS',
                'is_active' => true,
                'message_text' => "🎉 Pesanan *#{order_number}* berhasil dibuat!\n\nSilakan scan QRIS pada gambar di atas untuk membayar tagihan sebesar *Rp {total_tagihan}*.\nBatas waktu pembayaran adalah 1 jam dari sekarang.\n\nSistem akan otomatis mengirimkan notifikasi jika pembayaran sudah berhasil diterima.\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'riwayat_pesanan',
                'description' => 'Menampilkan daftar pesanan pelanggan',
                'is_active' => true,
                'message_text' => "📦 *Daftar Pesanan Kamu:*\n\n{daftar_pesanan}\n\nKetik 0 untuk 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'pesan_tidak_valid',
                'description' => 'Pesan fallback jika input user tidak sesuai dengan opsi yang ada',
                'is_active' => true,
                'message_text' => "Maaf Kak, pilihan tidak valid atau format salah. Silakan ketik angka yang sesuai dengan opsi di atas.\n\nKetik 0 untuk kembali."
            ]
        ];

        foreach ($templates as $template) {
            BotTemplate::create($template);
        }
    }
}
