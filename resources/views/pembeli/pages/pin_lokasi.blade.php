<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Titik Maps - Raya Kitchen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-3xl p-6 shadow-xl text-center">
        <h2 class="text-xl font-black text-gray-800 mb-2">Tandai Lokasi Rumahmu</h2>
        <p class="text-sm text-gray-500 mb-4">Geser peta untuk menentukan titik koordinat yang pas buat abang kurir, lalu klik Simpan.</p>

        <div id="map" class="w-full h-80 bg-gray-200 rounded-2xl mb-4 shadow-inner border-2 border-purple-200"></div>

        <button id="btn-simpan" class="w-full py-4 bg-[#C099CE] hover:bg-[#9B72AB] text-white font-black text-lg rounded-2xl shadow-lg transition-colors">
            Simpan Titik Ini
        </button>
    </div>

    <script>
        $(document).ready(function() {
            let addressId = "{{ $id }}";
            let userWa = "{{ $wa }}";
            let lat = -6.3473242; // Bekasi default
            let lng = 106.9481003;

            let map = L.map('map').setView([lat, lng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            let marker = L.marker([lat, lng], {draggable: true}).addTo(map);

            marker.on('dragend', function (e) {
                lat = marker.getLatLng().lat;
                lng = marker.getLatLng().lng;
            });
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                lat = e.latlng.lat;
                lng = e.latlng.lng;
            });

            $('#btn-simpan').click(function() {
                let btn = $(this);
                btn.text('Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: '/api/pembeli/alamat-update-koordinat',
                    type: 'POST',
                    data: {
                        id: addressId,
                        wa: userWa,
                        latitude: lat,
                        longitude: lng,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if(res.status === 'success') {
                            btn.text('Tersimpan! Balik ke WA.');
                            btn.removeClass('bg-[#C099CE]').addClass('bg-green-500');
                            setTimeout(() => {
                                window.location.href = "https://wa.me/628XXXXXXXXXX"; // Ganti sama nomor bot lo
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        alert('Gagal simpan koordinat. Pastikan link dibuka dari bot.');
                        btn.text('Coba Lagi').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
