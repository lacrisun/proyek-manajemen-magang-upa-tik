@extends('admin.layoutsadmin.main')
@section('title', 'Rekap Absensi Harian')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Rekap Absensi Harian</h1>

        {{-- Filter Tanggal --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="get" action="{{ route('admin.attendance.daily') }}"
                    class="d-flex align-items-end gap-3 flex-wrap">
                    <div>
                        <label for="date" class="form-label mb-1">Pilih Tanggal</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i> Tampilkan
                    </button>
                    <a href="{{ route('admin.attendance.daily') }}" class="btn btn-outline-secondary">
                        Hari Ini
                    </a>
                </form>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card shadow border-left-success h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Tepat Waktu</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $tepatWaktu->count() }} peserta</div>
                        </div>
                        <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow border-left-warning h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Terlambat</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $terlambat->count() }} peserta</div>
                        </div>
                        <i class="fa-solid fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-gray-800 mb-3">{{ $carbonDate->translatedFormat('l, d F Y') }}</h6>

        {{-- Tabel Tepat Waktu --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 fw-bold">
                    <i class="fa-solid fa-circle-check me-1"></i> Datang Tepat Waktu ({{ $tepatWaktu->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIM/NIS</th>
                                <th>Institusi</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tepatWaktu as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['participant']->nama }}</td>
                                    <td>{{ $row['participant']->nisnim ?? '-' }}</td>
                                    <td>{{ $row['participant']->institute->nama ?? '-' }}</td>
                                    <td>{{ $row['jam_masuk'] }}</td>
                                    <td>{{ $row['jam_keluar'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada peserta yang datang tepat waktu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel Terlambat --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-warning">
                <h6 class="m-0 fw-bold">
                    <i class="fa-solid fa-clock me-1"></i> Terlambat ({{ $terlambat->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIM/NIS</th>
                                <th>Institusi</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($terlambat as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['participant']->nama }}</td>
                                    <td>{{ $row['participant']->nisnim ?? '-' }}</td>
                                    <td>{{ $row['participant']->institute->nama ?? '-' }}</td>
                                    <td>{{ $row['jam_masuk'] }}</td>
                                    <td>{{ $row['jam_keluar'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada peserta yang terlambat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
