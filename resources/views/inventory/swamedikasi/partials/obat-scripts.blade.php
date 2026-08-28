<script>
    (function() {
        var searchUrl = '{{ route('inventory.swamedikasi.obat-search') }}';
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        var delayTimer = null;

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('obat-add-btn')) {
                var wrap = document.querySelector(e.target.dataset.target);
                var newRow = wrap.querySelector('.obat-row').cloneNode(true);
                newRow.querySelectorAll('input').forEach(function(el) { el.value = ''; });
                newRow.querySelector('.autocomplete-panel').style.display = 'none';
                wrap.appendChild(newRow);
                return;
            }

            if (e.target.classList.contains('btn-remove-obat')) {
                var wrap = e.target.closest('[id^="obat-wrap"]');
                if (wrap.querySelectorAll('.obat-row').length > 1) {
                    e.target.closest('.obat-row').remove();
                }
                return;
            }

            if (e.target.classList.contains('autocomplete-item')) {
                var row = e.target.closest('.obat-row');
                row.querySelector('.obat-nama').value = e.target.dataset.nama;
                row.querySelector('.obat-kd').value = e.target.dataset.kode;
                row.querySelector('.autocomplete-panel').style.display = 'none';
                return;
            }

            if (!e.target.closest('.autocomplete-wrapper')) {
                document.querySelectorAll('.autocomplete-panel').forEach(function(p) { p.style.display = 'none'; });
            }
        });

        document.addEventListener('input', function(e) {
            if (!e.target.classList.contains('obat-nama')) {
                return;
            }

            var input = e.target;
            var row = input.closest('.obat-row');
            row.querySelector('.obat-kd').value = '';
            var panel = row.querySelector('.autocomplete-panel');
            var keyword = input.value.trim();

            clearTimeout(delayTimer);
            if (keyword.length < 2) {
                panel.style.display = 'none';
                return;
            }

            delayTimer = setTimeout(function() {
                var form = new FormData();
                form.append('query', keyword);

                fetch(searchUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: form,
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        panel.innerHTML = '';
                        if (!data || data.length === 0) {
                            panel.innerHTML = '<div class="list-group-item">Obat tidak ditemukan</div>';
                            panel.style.display = 'block';
                            return;
                        }
                        data.forEach(function(item) {
                            var div = document.createElement('div');
                            div.className = 'list-group-item list-group-item-action autocomplete-item';
                            div.style.cursor = 'pointer';
                            div.dataset.kode = item.kd_barang;
                            div.dataset.nama = item.nm_barang;
                            div.textContent = item.nm_barang;
                            panel.appendChild(div);
                        });
                        panel.style.display = 'block';
                    });
            }, 300);
        });
    })();
</script>
