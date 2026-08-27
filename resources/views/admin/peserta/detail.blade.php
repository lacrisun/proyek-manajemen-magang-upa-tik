{{-- resources/views/admin/peserta/detail.blade.php --}}
@extends('admin.layoutsadmin.main')
@section('title', 'Detail Peserta')
@section('peserta-active', 'active')

@section('content')
    <div class="container-fluid">

        @if ($errors->any())
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: "{{ $errors->first() }}",
                    showConfirmButton: false,
                    timer: 4000
                });
            </script>
        @endif

        @if (session('success'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 4000
                });
            </script>
        @endif

        @if (session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: "{{ session('error') }}",
                    showConfirmButton: false,
                    timer: 4000
                });
            </script>
        @endif

        {{-- Header + Tombol Aksi --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-2 text-gray-800">Detail Peserta</h1>
            <div class="d-flex flex-wrap gap-2">
                {{-- Shortcut: Absensi Manual Izin/Sakit --}}
                <a href="{{ route('admin.attendance.manual.create', ['participant_id' => $participant->id]) }}"
                    class="btn btn-warning">
                    <i class="fas fa-clipboard-check me-1"></i> Input Izin/Sakit
                </a>

                <a href="{{ route('admin.peserta.edit', $participant->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.peserta.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-3">
            {{-- Kartu Informasi Peserta --}}
            <div class="col-12">
                <div class="card shadow-sm border-0 mb-2">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i> Informasi Peserta
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Nama Lengkap</label>
                                    <div class="info-value">{{ $participant->nama }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">NIK</label>
                                    <div class="info-value">{{ $participant->nik }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">NISN / NIM</label>
                                    <div class="info-value">{{ $participant->nisnim ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Jenis Kelamin</label>
                                    <div class="info-value">
                                        @if ($participant->jenis_kelamin === 'L')
                                            Laki-laki
                                        @elseif($participant->jenis_kelamin === 'P')
                                            Perempuan
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Sekolah/Instansi Asal</label>
                                    <div class="info-value">{{ $participant->institute->nama_instansi ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Jurusan</label>
                                    <div class="info-value">{{ $participant->jurusan ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Tahun Aktif</label>
                                    <div class="info-value">{{ $participant->tahun_aktif ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Kontak Peserta</label>
                                    <div class="info-value">{{ $participant->kontak_peserta ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Alamat</label>
                                    <div class="info-value">{{ $participant->alamat_asal ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <label class="info-label">Wali yang dapat dihubungi</label>
                                    <div class="info-value">
                                        {{ $participant->nama_wali ?? '-' }} ({{ $participant->kontak_wali ?? '-' }})
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="info-item">
                                    <label class="info-label">Keterangan</label>
                                    <div class="info-value">
                                        @if ($participant->keterangan)
                                            <div class="bg-light p-3 rounded">{{ $participant->keterangan }}</div>
                                        @else
                                            <span class="text-muted">Tidak ada keterangan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Perangkat Absensi --}}
            <div class="col-12">
                <div class="card shadow-sm border-0 mb-2">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-mobile-screen me-2"></i> Perangkat Absensi
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @php $device = $participant->registeredDevice; @endphp

                        @if ($device && $device->is_active)
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="mb-1">
                                        <strong>Perangkat terdaftar:</strong>
                                        {{ $device->device_model ?? 'Tidak diketahui' }}
                                    </div>
                                    <small class="text-muted">
                                        Didaftarkan pada:
                                        {{ optional($device->registered_at)->format('d M Y, H:i') ?? '-' }}
                                        @if ($device->last_used_at)
                                            &middot; Terakhir dipakai:
                                            {{ $device->last_used_at->format('d M Y, H:i') }}
                                        @endif
                                    </small>
                                </div>

                                <form method="post"
                                    action="{{ route('admin.peserta.device.reset', $participant->id) }}"
                                    onsubmit="return confirm('Reset perangkat terdaftar milik {{ $participant->nama }}? Peserta akan bisa mendaftarkan perangkat baru saat login berikutnya.');">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-rotate me-1"></i> Registrasi Ulang Perangkat
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-muted mb-0">
                                Peserta belum mendaftarkan perangkat untuk absensi.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tabs: Logbook & Absensi --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <ul class="nav nav-tabs" id="detailTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="logbook-tab" data-bs-toggle="tab"
                                    data-bs-target="#tab-logbook" type="button" role="tab">
                                    Logbook
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="absen-tab" data-bs-toggle="tab" data-bs-target="#tab-absen"
                                    type="button" role="tab">
                                    Riwayat Absensi
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">

                            {{-- ===== TAB LOGBOOK ===== --}}
                            <div class="tab-pane fade show active" id="tab-logbook" role="tabpanel"
                                aria-labelledby="logbook-tab">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <h5 class="mb-2 mb-md-0">Total Logbook: {{ $participant->logbooks->count() }}</h5>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-success btn-sm"
                                            href="{{ route('admin.peserta.logbook.export.excel', $participant->id) }}">
                                            <i class="fas fa-file-excel me-1"></i> Export Excel
                                        </a>
                                        <a class="btn btn-danger btn-sm"
                                            href="{{ route('admin.peserta.logbook.export.pdf', $participant->id) }}">
                                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                                        </a>
                                    </div>
                                </div>

                                @if ($participant->logbooks->isEmpty())
                                    <div class="text-muted">Belum ada logbook.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:80px;">Tanggal</th>
                                                    <th style="min-width:240px;">Tugas/Dikerjakan</th>
                                                    <th>Deskripsi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($participant->logbooks->sortByDesc('date') as $lb)
                                                    <tr>
                                                        <td>{{ optional($lb->date)->format('d M Y') ?? '-' }}</td>
                                                        <td>{{ $lb->tasks_completed }}</td>
                                                        <td>{{ $lb->description }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- ===== TAB ABSENSI ===== --}}
                            <div class="tab-pane fade" id="tab-absen" role="tabpanel" aria-labelledby="absen-tab">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <h5 class="mb-2 mb-md-0">Total Absen: {{ $participant->attendances->count() }}</h5>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-success btn-sm"
                                            href="{{ route('admin.peserta.absen.export.excel', $participant->id) }}">
                                            <i class="fas fa-file-excel me-1"></i> Export Excel
                                        </a>
                                        <a class="btn btn-danger btn-sm"
                                            href="{{ route('admin.peserta.absen.export.pdf', $participant->id) }}">
                                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                                        </a>
                                    </div>
                                </div>

                                @if ($participant->attendances->isEmpty())
                                    <div class="text-muted">Belum ada riwayat absen.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:120px;">Tanggal</th>
                                                    <th style="width:110px;">Status</th>
                                                    <th style="width:100px;">Check-in</th>
                                                    <th style="width:100px;">Check-out</th>
                                                    <th>IP Masuk</th>
                                                    <th>IP Pulang</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($participant->attendances->sortByDesc('date') as $ab)
                                                    @php
                                                        $badge =
                                                            $ab->status === 'Hadir'
                                                                ? 'bg-success'
                                                                : ($ab->status === 'Izin'
                                                                    ? 'bg-warning'
                                                                    : ($ab->status === 'Sakit'
                                                                        ? 'bg-info'
                                                                        : 'bg-secondary'));
                                                    @endphp
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($ab->date)->translatedFormat('d M Y') }}
                                                        </td>
                                                        <td><span
                                                                class="badge {{ $badge }}">{{ $ab->status }}</span>
                                                        </td>
                                                        <td>{{ $ab->check_in_time ? $ab->check_in_time->setTimezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                                                        </td>
                                                        <td>{{ $ab->check_out_time ? $ab->check_out_time->setTimezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                                                        </td>
                                                        <td>{{ $ab->check_in_ip_address ?? '-' }}</td>
                                                        <td>{{ $ab->check_out_ip_address ?? '-' }}</td>
                                                        <td>{{ $ab->note ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                        </div> {{-- tab-content --}}
                    </div>
                </div>
            </div>

        </div> {{-- row --}}
    </div>

    {{-- Minimal CSS --}}
    <style>
        .info-item {
            margin-bottom: .75rem;
        }

        .info-label {
            font-size: .9rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: .25rem;
            display: block;
        }

        .info-value {
            font-size: 1rem;
            color: #343a40;
            min-height: 1.2rem;
        }

        .card {
            border-radius: .5rem;
        }

        .card-header {
            border-bottom: none;
        }
    </style>
@endsection
