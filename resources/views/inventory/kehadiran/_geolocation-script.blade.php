{{-- Ambil lokasi HP sebelum submit form check-in/check-out ber-class "js-geo-form",
     lalu suntik sebagai hidden input lat/lng. Dipakai bareng di checkin.blade.php
     dan index.blade.php (tombol Check-Out) supaya logikanya tidak dobel. --}}
<script>
    document.querySelectorAll('.js-geo-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = form.querySelector('button[type=submit]');
            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Mengambil lokasi...';

            if (!navigator.geolocation) {
                alert('Browser/HP Anda tidak mendukung akses lokasi. Aksi ini tidak bisa dilanjutkan.');
                btn.disabled = false;
                btn.innerHTML = originalText;
                return;
            }

            navigator.geolocation.getCurrentPosition(function (pos) {
                var latInput = document.createElement('input');
                latInput.type = 'hidden';
                latInput.name = 'lat';
                latInput.value = pos.coords.latitude;
                form.appendChild(latInput);

                var lngInput = document.createElement('input');
                lngInput.type = 'hidden';
                lngInput.name = 'lng';
                lngInput.value = pos.coords.longitude;
                form.appendChild(lngInput);

                form.submit();
            }, function (err) {
                alert('Gagal mengambil lokasi Anda. Aktifkan izin lokasi di HP/browser lalu coba lagi. (' + err.message + ')');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            });
        });
    });
</script>
