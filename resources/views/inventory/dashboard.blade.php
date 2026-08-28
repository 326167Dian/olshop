@extends('inventory.layouts.app')

@section('header', 'Dashboard')

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">Hai, {{ auth('admin')->user()->nama_lengkap }} 👋</h5>
            <p class="mb-0 text-muted">
                Selamat datang di Sistem Inventory Apotek. Silakan pilih menu di sebelah kiri untuk mengelola
                aplikasi.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0" id="sales-chart-title">Penjualan {{ now()->format('F Y') }}</h5>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" id="sales-type-penjualan" class="btn btn-primary" data-tipe="penjualan">Penjualan</button>
                    <button type="button" id="sales-type-swamedikasi" class="btn btn-outline-primary" data-tipe="swamedikasi">Swamedikasi</button>
                </div>
                <select id="sales-filter-month" class="form-select form-select-sm" style="width: auto; min-width: 80px;">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                    @endfor
                </select>
                <select id="sales-filter-year" class="form-select form-select-sm" style="width: auto; min-width: 100px;">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-primary-subtle">
                        <div class="text-muted small">Total Bulan Dipilih</div>
                        <div class="fs-5 fw-bold" id="sales-total-current">0</div>
                    </div>
                </div>
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-warning-subtle">
                        <div class="text-muted small">Total Bulan Lalu</div>
                        <div class="fs-5 fw-bold" id="sales-total-previous">0</div>
                    </div>
                </div>
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-success-subtle">
                        <div class="text-muted small">Perubahan</div>
                        <div class="fs-5 fw-bold" id="sales-total-growth">0%</div>
                    </div>
                </div>
            </div>
            <div style="position: relative; height: 320px;">
                <canvas id="sales-chart"></canvas>
            </div>
            <small id="sales-chart-updated" class="text-muted d-block mt-2">Memuat data...</small>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    (function() {
        var salesChart = null;
        var salesChartTipe = 'penjualan';
        var salesChartFormat = 'rupiah';

        function formatAngka(nilai, format) {
            var n = parseFloat(nilai || 0);
            if (format === 'rupiah') {
                return new Intl.NumberFormat('id-ID').format(n);
            }
            return n.toString();
        }

        function updateSummary(items, previousItems, format) {
            var totalCurrent = 0;
            var totalPrevious = 0;

            (items || []).forEach(function(i) { totalCurrent += parseFloat(i.nilai || 0); });
            (previousItems || []).forEach(function(i) { totalPrevious += parseFloat(i.nilai || 0); });

            var growth = 0;
            if (totalPrevious > 0) {
                growth = ((totalCurrent - totalPrevious) / totalPrevious) * 100;
            } else if (totalCurrent > 0) {
                growth = 100;
            }

            var growthLabel = (growth >= 0 ? '+' : '') + growth.toFixed(2) + '%';

            document.getElementById('sales-total-current').textContent = formatAngka(totalCurrent, format);
            document.getElementById('sales-total-previous').textContent = formatAngka(totalPrevious, format);
            document.getElementById('sales-total-growth').textContent = growthLabel;
        }

        function renderChart(items, previousItems, previousLabel, format) {
            var labels = (items || []).map(function(i) {
                var parts = (i.tanggal || '').split('-');
                return parts.length === 3 ? parts[2] : i.tanggal;
            });
            var dataCurrent = (items || []).map(function(i) { return parseFloat(i.nilai || 0); });
            var dataPrevious = (previousItems || []).map(function(i) { return parseFloat(i.nilai || 0); });

            var ctx = document.getElementById('sales-chart').getContext('2d');

            if (salesChart) {
                salesChart.data.labels = labels;
                salesChart.data.datasets[0].data = dataCurrent;
                salesChart.data.datasets[1].data = dataPrevious;
                salesChart.data.datasets[1].label = previousLabel || 'Bulan Lalu';
                salesChart.update();
                return;
            }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bulan Dipilih',
                        data: dataCurrent,
                        borderColor: '#3c8dbc',
                        backgroundColor: 'rgba(60,141,188,0.15)',
                        fill: true,
                        tension: 0.3,
                    }, {
                        label: previousLabel || 'Bulan Lalu',
                        data: dataPrevious,
                        borderColor: '#f39c12',
                        backgroundColor: 'rgba(243,156,18,0.05)',
                        fill: false,
                        tension: 0.3,
                        borderDash: [5, 5],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return formatAngka(value, format); }
                            }
                        }
                    }
                }
            });
        }

        function loadSalesChart() {
            var bulan = document.getElementById('sales-filter-month').value;
            var tahun = document.getElementById('sales-filter-year').value;

            fetch("{{ route('inventory.sales-chart-data') }}?bulan=" + bulan + "&tahun=" + tahun + "&tipe=" + salesChartTipe, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(response) {
                    if (!response.status) {
                        document.getElementById('sales-chart-updated').textContent = response.message || 'Data tidak tersedia.';
                        return;
                    }

                    salesChartFormat = response.format;
                    renderChart(response.data, response.data_bulan_lalu, response.periode_sebelumnya_label, response.format);
                    updateSummary(response.data, response.data_bulan_lalu, response.format);

                    var judulTipe = response.judul_tipe || 'Penjualan';
                    document.getElementById('sales-chart-title').textContent = judulTipe + ' ' + (response.periode_label || '');
                    document.getElementById('sales-chart-updated').textContent = 'Update terakhir: ' + response.updated_at;
                })
                .catch(function() {
                    document.getElementById('sales-chart-updated').textContent = 'Gagal memuat data grafik.';
                });
        }

        document.getElementById('sales-filter-month').addEventListener('change', loadSalesChart);
        document.getElementById('sales-filter-year').addEventListener('change', loadSalesChart);

        document.getElementById('sales-type-penjualan').addEventListener('click', function() {
            if (salesChartTipe === 'penjualan') return;
            salesChartTipe = 'penjualan';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            document.getElementById('sales-type-swamedikasi').classList.remove('btn-primary');
            document.getElementById('sales-type-swamedikasi').classList.add('btn-outline-primary');
            loadSalesChart();
        });

        document.getElementById('sales-type-swamedikasi').addEventListener('click', function() {
            if (salesChartTipe === 'swamedikasi') return;
            salesChartTipe = 'swamedikasi';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            document.getElementById('sales-type-penjualan').classList.remove('btn-primary');
            document.getElementById('sales-type-penjualan').classList.add('btn-outline-primary');
            loadSalesChart();
        });

        loadSalesChart();
    })();
</script>
@endpush
