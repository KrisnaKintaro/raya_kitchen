<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BotTemplate;

class BotTemplateSeeder extends Seeder
{
    /**
     * Master Blueprint Default Pesan Bot WhatsApp (Opsi 1 - Tanpa membebani DB)
     */
    public static function getDefaults(): array
    {
        return [
            // A. MENU UTAMA & KATALOG
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
                'message_text' => "Cara pesannya gampang banget Kak!\n\n1. Pilih 'Lihat Katalog' lalu ketik angka produk yang mau dibeli.\n2. Masukkan jumlah produk.\n3. Ikuti langkah pengisian data pengiriman.\n4. Pilih metode pembayaran (Lunas, DP, atau COD).\n5. Lakukan pembayaran via link QRIS dan pesanan otomatis diproses!\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'lihat_kategori',
                'description' => 'Menampilkan daftar kategori produk yang aktif',
                'is_active' => true,
                'message_text' => "Silakan pilih kategori produk yang mau dilihat:\n\n{daftar_kategori}\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'kategori_kosong',
                'description' => 'Pesan saat belum ada kategori produk yang aktif',
                'is_active' => true,
                'message_text' => "Maaf Kak, saat ini belum ada kategori produk yang aktif.\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'lihat_varian',
                'description' => 'Menampilkan daftar varian dari kategori yang dipilih',
                'is_active' => true,
                'message_text' => "Kategori: *{nama_kategori}*\nPilih varian yang mau dilihat detailnya:\n\n{daftar_varian}\n0. 🔙 Kembali ke Daftar Kategori"
            ],
            [
                'session_state' => 'varian_kosong',
                'description' => 'Pesan saat kategori belum memiliki varian produk',
                'is_active' => true,
                'message_text' => "Maaf Kak, belum ada varian produk untuk kategori *{nama_kategori}*.\n\n0. 🔙 Kembali ke Daftar Kategori"
            ],
            [
                'session_state' => 'detail_varian',
                'description' => 'Menampilkan detail produk dan opsi tambah ke keranjang',
                'is_active' => true,
                'message_text' => "Detail Produk:\n\n🍞 *{kategori_produk} - {nama_produk}*\n🏷️ Kategori: {kategori_produk}\n💰 Harga: Rp {harga_produk}\n\n📝 Deskripsi:\n{deskripsi_produk}\n\nKetik angka berikut:\n1. 🛒 Tambah ke Keranjang\n0. 🔙 Kembali ke Daftar Varian"
            ],
            [
                'session_state' => 'input_qty',
                'description' => 'Meminta jumlah produk yang ingin dimasukkan ke keranjang',
                'is_active' => true,
                'message_text' => "Berapa banyak *{nama_produk}* yang mau dipesan? (Ketik angkanya saja, misal: 2)\n\n0. 🔙 Batal & Kembali"
            ],
            [
                'session_state' => 'input_qty_invalid',
                'description' => 'Pesan saat input jumlah produk bukan angka valid',
                'is_active' => true,
                'message_text' => "Kak, tolong masukin angka jumlah yang valid ya (misal: 2). Atau ketik 0 buat batal."
            ],

            // B. KERANJANG & CHECKOUT DATA DIRI / ALAMAT
            [
                'session_state' => 'keranjang',
                'description' => 'Menampilkan isi keranjang belanja dan opsi checkout',
                'is_active' => true,
                'message_text' => "🛒 *Isi Keranjang Kamu:*\n\n{isi_keranjang}\nTotal Belanja: Rp {total_harga}\n\nPilih aksi:\n1. ✅ Lanjut Checkout\n2. ➕ Tambah Produk Lain\n3. 🗑️ Kosongkan Keranjang\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'keranjang_kosong',
                'description' => 'Pesan saat pelanggan membuka keranjang tapi masih kosong',
                'is_active' => true,
                'message_text' => "Keranjang belanja kamu masih kosong nih Kak.\n\nKetik 0 untuk 🔙 Kembali ke Menu Utama."
            ],
            [
                'session_state' => 'keranjang_dikosongkan',
                'description' => 'Pesan konfirmasi setelah isi keranjang dikosongkan',
                'is_active' => true,
                'message_text' => "🗑️ Keranjang belanja kamu sudah dikosongkan ya Kak!"
            ],
            [
                'session_state' => 'checkout_nama',
                'description' => 'Meminta nama penerima saat mulai checkout',
                'is_active' => true,
                'message_text' => "Mulai proses checkout ya!\n\nAtas nama siapa pesanan ini? (Ketik nama penerima)\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_alamat',
                'description' => 'Meminta alamat pengiriman dari daftar tersimpan atau form baru',
                'is_active' => true,
                'message_text' => "Kirim ke alamat mana Kak?\n\n{daftar_alamat}\nKetik *angka* dari list alamat di atas.\nAtau kalau mau buat alamat baru, *copy-paste* & isi form ini:\n\n{form_alamat}\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_alamat_kosong',
                'description' => 'Meminta pelanggan mengisi form alamat baru karena belum punya alamat',
                'is_active' => true,
                'message_text' => "Kak, alamat pengiriman kamu masih kosong nih.\nSilakan *copy-paste* dan isi form di bawah ini lalu kirim balik ke sini ya:\n\n{form_alamat}\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'form_alamat_tidak_lengkap',
                'description' => 'Pesan saat form alamat baru yang dikirim pelanggan belum lengkap',
                'is_active' => true,
                'message_text' => "Format form alamat belum lengkap Kak. Pastikan *Provinsi, Kota/Kabupaten, Kecamatan, dan Alamat Jalan* sudah terisi setelah tanda titik dua (:).\n\nSilakan copy dan isi ulang form berikut:\n\n{form_alamat}\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'alamat_tersimpan_pin_maps',
                'description' => 'Pesan berisi link untuk menentukan titik Maps setelah simpan alamat baru',
                'is_active' => true,
                'message_text' => "✅ Alamat berhasil disimpan dan dijadikan alamat utama!\n\nLangkah terakhir, silakan klik link di bawah ini untuk menentukan titik akurat di Maps agar sistem bisa menghitung ongkos kirimnya:\n{link_maps}"
            ],
            [
                'session_state' => 'alamat_belum_pin',
                'description' => 'Pesan saat alamat yang dipilih belum memiliki koordinat Maps',
                'is_active' => true,
                'message_text' => "Koordinat alamat ini belum diset Kak.\n\nSilakan klik link berikut untuk menentukan titik akurat di Maps:\n{link_maps}"
            ],

            // C. ONGKIR, JADWAL & PEMBAYARAN
            [
                'session_state' => 'loading_cek_ongkir',
                'description' => 'Pesan tunggu saat bot mengecek jarak & tarif ekspedisi',
                'is_active' => true,
                'message_text' => "Tunggu sebentar ya Kak, sistem sedang mengecek jarak & tarif pengiriman ke alamatmu... 🚚"
            ],
            [
                'session_state' => 'info_gratis_ongkir',
                'description' => 'Pesan pemberitahuan ketika alamat masuk jangkauan Gratis Ongkir Kurir Internal',
                'is_active' => true,
                'message_text' => "Asyik! Jarak lokasi kamu ({jarak} KM) masuk area *Gratis Ongkir* Kurir Internal Raya Kitchen. Yuk lanjut pilih jadwal pengirimannya!"
            ],
            [
                'session_state' => 'checkout_pilih_ekspedisi',
                'description' => 'Menampilkan pilihan tarif kurir ekspedisi (di luar jarak gratis ongkir)',
                'is_active' => true,
                'message_text' => "Alamatmu berada di luar jangkauan kurir internal. Silakan pilih layanan ekspedisi berikut Kak:\n\n{daftar_ekspedisi}\nBalas dengan *angka pilihanmu*.\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'ekspedisi_invalid',
                'description' => 'Pesan saat nomor pilihan ekspedisi tidak sesuai',
                'is_active' => true,
                'message_text' => "Pilihan kurir tidak valid Kak. Balas dengan nomor kurir yang ada di daftar, atau ketik 0 buat batal."
            ],
            [
                'session_state' => 'ekspedisi_tidak_tersedia',
                'description' => 'Pesan saat tidak ada kurir ekspedisi yang mendukung alamat tujuan',
                'is_active' => true,
                'message_text' => "Maaf Kak, layanan kurir ekspedisi belum tersedia untuk tujuan alamatmu.\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'ongkir_gangguan',
                'description' => 'Pesan saat API RajaOngkir sedang sibuk/gangguan',
                'is_active' => true,
                'message_text' => "Sistem pengecekan ongkir sedang sibuk Kak. Silakan tunggu beberapa saat lalu pilih menu Keranjang & Checkout kembali, atau lanjutkan pemesanan via Website ya. 🙏\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'checkout_tanggal',
                'description' => 'Meminta input tanggal pengiriman untuk Kurir Internal',
                'is_active' => true,
                'message_text' => "Kapan pesanan mau dikirim/diambil?\n(Ketik dengan format *YYYY-MM-DD*, contoh: {contoh_tanggal})\n*Minimal pemesanan H+1 dari hari ini*\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'tanggal_format_salah',
                'description' => 'Pesan saat format tanggal yang diketik salah',
                'is_active' => true,
                'message_text' => "Format atau pilihan tanggal belum sesuai Kak. Ketik dengan format *YYYY-MM-DD* (contoh: 2026-11-20), pilih angka dari daftar rekomendasi (jika ada), atau ketik 0 untuk batal."
            ],
            [
                'session_state' => 'tanggal_tolak_minimal',
                'description' => 'Alasan penolakan ketika tanggal kurang dari H+1',
                'is_active' => true,
                'message_text' => "Maaf Kak, tanggal pengiriman minimal H+1 (besok) dari hari pemesanan."
            ],
            [
                'session_state' => 'tanggal_tolak_libur',
                'description' => 'Alasan penolakan ketika tanggal yang dipilih adalah hari libur toko',
                'is_active' => true,
                'message_text' => "Maaf Kak, pada tanggal *{tanggal}* dapur Raya Kitchen sedang libur operasional."
            ],
            [
                'session_state' => 'tanggal_tolak_penuh',
                'description' => 'Alasan penolakan ketika kuota produksi harian pada tanggal tersebut penuh',
                'is_active' => true,
                'message_text' => "Waduh, kuota produksi dapur pada tanggal *{tanggal}* sudah penuh untuk jumlah pesananmu (Sisa kuota: {sisa_kuota} pcs)."
            ],
            [
                'session_state' => 'rekomendasi_tanggal',
                'description' => 'Menampilkan daftar rekomendasi tanggal terdekat yang masih kosong',
                'is_active' => true,
                'message_text' => "{alasan_penolakan}\n\nBerikut rekomendasi tanggal terdekat yang kuotanya masih muat untuk pesananmu Kak:\n\n{daftar_tanggal}\nBalas dengan *angka pilihan* di atas, atau ketik manual *tanggal lain (YYYY-MM-DD)*.\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_jam',
                'description' => 'Meminta jam pengiriman sesuai jam operasional StoreSetting',
                'is_active' => true,
                'message_text' => "Jam berapa pesanan mau dikirim/diambil Kak?\n(Jam operasional kurir: *{jam_buka} sampai {jam_tutup} WIB*, ketik contoh: 14:00)\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'jam_format_salah',
                'description' => 'Pesan saat format jam yang diketik salah',
                'is_active' => true,
                'message_text' => "Format atau pilihan jam belum sesuai Kak. Ketik jam antara *{jam_buka} - {jam_tutup}* (contoh: 14:00), pilih angka dari rekomendasi, atau ketik 0 untuk batal."
            ],
            [
                'session_state' => 'jam_tolak_operasional',
                'description' => 'Alasan penolakan ketika jam berada di luar jam operasional',
                'is_active' => true,
                'message_text' => "Maaf Kak, jam yang dipilih berada di luar jam operasional kurir kami (*{jam_buka} - {jam_tutup} WIB*)."
            ],
            [
                'session_state' => 'jam_tolak_penuh',
                'description' => 'Alasan penolakan ketika slot pengiriman pada jam tersebut penuh',
                'is_active' => true,
                'message_text' => "Waduh, jadwal pengiriman pada jam *{jam} WIB* sudah penuh Kak."
            ],
            [
                'session_state' => 'rekomendasi_jam',
                'description' => 'Menampilkan rekomendasi jam operasional yang masih kosong',
                'is_active' => true,
                'message_text' => "{alasan_penolakan}\n\nBerikut jam operasional yang masih kosong di tanggal *{tanggal}*:\n\n{daftar_jam}\nBalas dengan *angka pilihan*, ketik manual *jam lain (HH:MM)*, atau ketik 0 untuk batal."
            ],
            [
                'session_state' => 'checkout_metode_bayar',
                'description' => 'Memilih skema pembayaran (Lunas, DP 30%, atau COD)',
                'is_active' => true,
                'message_text' => "Pilih skema pembayaran:\n\n1. 💳 Bayar Lunas (100% via QRIS)\n2. ⏳ DP Dulu (30% via QRIS, sisa via sistem)\n3. 🤝 COD (Bayar di tempat, wajib transfer DP 30%)\n\n0. 🔙 Batal Checkout"
            ],
            [
                'session_state' => 'checkout_konfirmasi',
                'description' => 'Menampilkan ringkasan final sebelum membuat pesanan',
                'is_active' => true,
                'message_text' => "🧾 *Ringkasan Pesanan*\n{ringkasan_pesanan}\n\n*Tagihan Saat Ini: Rp {total_tagihan}*\n\nApakah data pesanan sudah benar?\n1. ✅ Ya, Buat Pesanan & Bayar\n2. ❌ Tidak, Batal Checkout"
            ],
            [
                'session_state' => 'pembayaran_qris',
                'description' => 'Pesan instruksi pembayaran beserta link QRIS dan Nota Digital',
                'is_active' => true,
                'message_text' => "🎉 Pesanan *#{order_number}* berhasil dibuat!\n\nTotal Tagihan Saat Ini: *Rp {total_tagihan}*\n\nSilakan klik link di bawah ini untuk membayar via QRIS:\n👉 {link_pembayaran}\n\nLihat Nota Digital:\n🧾 {link_nota}\n\nKetik 0 untuk kembali ke Menu Utama."
            ],
            [
                'session_state' => 'pembayaran_gangguan',
                'description' => 'Pesan saat pembuatan invoice QRIS mengalami kendala',
                'is_active' => true,
                'message_text' => "Maaf Kak, terjadi kendala saat memproses pesananmu. Silakan coba lagi ya."
            ],

            // D. RIWAYAT PESANAN, BAYAR ULANG & PEMBATALAN VIA BOT
            [
                'session_state' => 'riwayat_pesanan',
                'description' => 'Menampilkan daftar riwayat pesanan dengan pagination & instruksi filter',
                'is_active' => true,
                'message_text' => "📦 *Filter: {label_filter}*\n📄 *Halaman {halaman_sekarang} dari {total_halaman}* (Total: {total_pesanan} pesanan)\n\n{daftar_pesanan}\n\n──────────────────\n{navigasi_halaman}🔍 *Filter Status:* ketik `semua`, `unpaid`, `dp`, `lunas`, `pending`, `diproses`, `siap dikirim`, `selesai`, atau `batal`\n💳 *Bayar Ulang / Pelunasan Sisa DP:* ketik `bayar [nomor]` (contoh: `bayar 1`)\n❌ *Batalkan Pesanan Pending:* ketik `batalkan [nomor]` (contoh: `batalkan 1`)\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'riwayat_pesanan_kosong',
                'description' => 'Pesan saat tidak ada pesanan pada kategori filter yang dipilih',
                'is_active' => true,
                'message_text' => "📦 *Filter: {label_filter}*\n\nBelum ada pesanan di kategori ini Kak.\n\n🔍 *Ketik Filter Lain:*\n`semua` | `unpaid` | `dp` | `lunas` | `pending` | `diproses` | `siap dikirim` | `selesai` | `batal`\n\n0. 🔙 Kembali ke Menu Utama"
            ],
            [
                'session_state' => 'riwayat_perintah_salah',
                'description' => 'Panduan saat perintah yang diketik di menu riwayat pesanan tidak dikenali',
                'is_active' => true,
                'message_text' => "Perintah tidak dikenali Kak.\n\n- Ketik *lanjut* / *prev* untuk pindah halaman\n- Ketik filter: `semua`, `unpaid`, `dp`, `lunas`, `pending`, `diproses`, `siap dikirim`, `selesai`, `batal`\n- Ketik `bayar [nomor]` untuk bayar/lunasi\n- Ketik `batalkan [nomor]` untuk membatalkan pesanan\n- Ketik *0* untuk ke Menu Utama."
            ],
            [
                'session_state' => 'riwayat_bayar_ulang',
                'description' => 'Mengirimkan link pembayaran QRIS saat pelanggan mengetik bayar [nomor]',
                'is_active' => true,
                'message_text' => "💳 *Pembayaran Pesanan #{order_number}*\nTagihan: *Rp {total_tagihan}*\n\nSilakan klik link berikut untuk scan QRIS:\n👉 {link_pembayaran}\n\nKetik *0* untuk kembali ke Menu Utama, atau ketik filter/navigasi untuk lanjut cek pesanan."
            ],
            [
                'session_state' => 'batal_tidak_ditemukan',
                'description' => 'Pesan saat nomor pesanan yang ingin dibatalkan tidak ditemukan',
                'is_active' => true,
                'message_text' => "⚠️ Pesanan *#{order_number}* tidak ditemukan Kak."
            ],
            [
                'session_state' => 'batal_status_tidak_bisa',
                'description' => 'Pesan saat pesanan sudah diproses sehingga tidak bisa dibatalkan',
                'is_active' => true,
                'message_text' => "⚠️ Maaf Kak, pesanan *#{order_number}* statusnya sudah *{status_pesanan}* sehingga tidak bisa dibatalkan."
            ],
            [
                'session_state' => 'batal_pesanan_alasan',
                'description' => 'Meminta alasan pembatalan pesanan',
                'is_active' => true,
                'message_text' => "⚠️ *Batalkan Pesanan #{order_number}*\n\nPilih alasan pembatalan dengan mengetik angkanya:\n1. Ingin merubah rincian pesanan (alamat/item)\n2. Jadwal pengiriman terlalu lama/tidak sesuai\n3. Berubah pikiran\n\nAtau ketik langsung alasan pembatalanmu.\n0. 🔙 Batal & Kembali ke Daftar Pesanan"
            ],
            [
                'session_state' => 'batal_pesanan_refund',
                'description' => 'Meminta nomor rekening refund untuk pembatalan pesanan yang sudah DP/Lunas',
                'is_active' => true,
                'message_text' => "💳 *Data Pengembalian Dana (Refund)*\nKarena pesanan *#{order_number}* sudah memiliki pembayaran masuk, silakan ketik data rekening/E-Wallet untuk refund dengan format:\n\n*Nama Bank/E-Wallet - Nomor Rekening - Atas Nama*\nContoh: `BCA - 1234567890 - Budi Santoso`\n\n0. 🔙 Batal & Kembali"
            ],
            [
                'session_state' => 'batal_refund_format_salah',
                'description' => 'Pesan saat format input rekening refund salah',
                'is_active' => true,
                'message_text' => "Format rekening belum sesuai Kak. Pastikan dipisah tanda strip (-) menjadi 3 bagian:\n*Nama Bank - Nomor Rekening - Atas Nama*\nContoh: `BCA - 1234567890 - Budi Santoso`\n\nAtau ketik 0 untuk kembali."
            ],
            [
                'session_state' => 'batal_pesanan_berhasil',
                'description' => 'Pesan konfirmasi setelah pesanan berhasil dibatalkan',
                'is_active' => true,
                'message_text' => "✅ Pesanan *#{order_number}* berhasil dibatalkan.\n{info_refund}"
            ],
            [
                'session_state' => 'pesan_tidak_valid',
                'description' => 'Pesan fallback umum jika input pelanggan tidak sesuai opsi',
                'is_active' => true,
                'message_text' => "Maaf Kak, pilihan tidak valid atau format salah. Silakan ketik angka yang sesuai dengan opsi di atas.\n\nKetik 0 untuk kembali."
            ],

            // E. NOTIFIKASI OTOMATIS (SATPAM PEMBAYARAN & UPDATE STATUS DAPUR)
            [
                'session_state' => 'notif_pembayaran_lunas',
                'description' => 'Notifikasi WA otomatis saat pembayaran Lunas 100% diterima',
                'is_active' => true,
                'message_text' => "✅ *Pembayaran Berhasil (LUNAS)*\n\nHalo Kak *{nama_penerima}*,\nPembayaran untuk pesanan *#{order_number}* sebesar *{total_pesanan}* telah berhasil kami terima!\n\nPesananmu kini masuk antrean dapur kami.\n\n🧾 *Lihat Nota Digital:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_pembayaran_dp',
                'description' => 'Notifikasi WA otomatis saat pembayaran DP 30% diterima',
                'is_active' => true,
                'message_text' => "✅ *Pembayaran DP 30% Berhasil*\n\nHalo Kak *{nama_penerima}*,\nPembayaran DP sebesar *{nominal_dp}* untuk pesanan *#{order_number}* telah diterima!\nSisa tagihan sebesar *{sisa_tagihan}* dapat dilunasi maksimal saat pesanan sudah siap diantar/diambil.\n\n🧾 *Lihat Nota Digital & Status:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_pelunasan_berhasil',
                'description' => 'Notifikasi WA otomatis saat pelunasan sisa tagihan 70% diterima',
                'is_active' => true,
                'message_text' => "🎉 *Pelunasan Berhasil (LUNAS 100%)*\n\nHalo Kak *{nama_penerima}*,\nPelunasan tagihan untuk pesanan *#{order_number}* telah berhasil kami terima! {info_status}\n\n🧾 *Lihat Nota Digital:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_pembayaran_gagal',
                'description' => 'Notifikasi WA otomatis saat pembayaran QRIS gagal',
                'is_active' => true,
                'message_text' => "⚠️ *Pembayaran Belum Berhasil*\n\nHalo Kak *{nama_penerima}*,\nPembayaran QRIS untuk pesanan *#{order_number}* gagal diproses atau melewati batas waktu.\n\n🧾 *Cek Detail Nota:*\n{link_nota}\n👉 *Bayar Ulang QRIS:*\n{link_pembayaran}"
            ],
            [
                'session_state' => 'notif_batal_otomatis',
                'description' => 'Notifikasi WA otomatis saat pesanan dibatalkan karena lewat 24 jam belum dibayar',
                'is_active' => true,
                'message_text' => "❌ *Pesanan Dibatalkan Otomatis*\n\nHalo Kak *{nama_penerima}*,\nMohon maaf, pesananmu dengan nomor *#{order_number}* telah dibatalkan otomatis oleh sistem karena melewati batas waktu pembayaran 24 jam.\n\n🧾 *Detail Nota:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_menunggu_pelunasan',
                'description' => 'Notifikasi WA otomatis pengingat pelunasan sisa tagihan DP',
                'is_active' => true,
                'message_text' => "⏳ *Menunggu Pelunasan Tagihan*\n\nHalo Kak *{nama_penerima}*,\nKabar baik! Pesananmu *#{order_number}* sudah *SIAP DIANTAR/DIAMBIL*.\n\nKarena kamu menggunakan skema DP, masih ada sisa tagihan sebesar *{sisa_tagihan}*. Yuk segera lunasi lewat link di bawah agar pesanan bisa langsung diselesaikan:\n\n👉 *Link Pelunasan QRIS:*\n{link_pembayaran}\n\n🧾 *Lihat Nota Digital:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_diproses',
                'description' => 'Notifikasi WA otomatis saat pesanan mulai diproses dapur',
                'is_active' => true,
                'message_text' => "👨‍🍳 *Pesanan Sedang Diproses*\n\nHalo Kak *{nama_penerima}*,\nPesananmu dengan nomor *#{order_number}* saat ini sedang kami *PROSES* di dapur Raya Kitchen.\n\n🧾 *Pantau Nota Digital:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_diantar',
                'description' => 'Notifikasi WA otomatis saat pesanan siap diantar/diambil',
                'is_active' => true,
                'message_text' => "🚚 *Pesanan Siap Diantar / Diambil*\n\nHalo Kak *{nama_penerima}*,\nPesananmu dengan nomor *#{order_number}* sudah *SIAP* dan sedang dalam perjalanan / menunggu diambil!\n\n{info_pelunasan}\n\n🧾 *Lihat Nota Digital:*\n{link_nota}"
            ],
            [
                'session_state' => 'notif_selesai',
                'description' => 'Notifikasi WA otomatis saat pesanan telah selesai',
                'is_active' => true,
                'message_text' => "🥳 *Pesanan Selesai*\n\nHalo Kak *{nama_penerima}*,\nTransaksi untuk pesanan *#{order_number}* telah *SELESAI*.\n\nTerima kasih banyak sudah berbelanja di Raya Kitchen. Ditunggu pesanan selanjutnya ya!\n\n🧾 *Nota Digital:*\n{link_nota}"
            ]
        ];
    }

    public function run(): void
    {
        BotTemplate::truncate();

        foreach (self::getDefaults() as $template) {
            BotTemplate::create($template);
        }
    }
}