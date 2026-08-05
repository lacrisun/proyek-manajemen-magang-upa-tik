@extends('pemagang.layoutspemagang.main')
@section('title', 'Riwayat Absensi')
@section('absensi-active', 'active')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Riwayat Absensi Saya</h1>

        @if (session('status'))
            <div class="alert {{ session('isError') ? 'alert-danger' : 'alert-success' }} alert-dismissible fade show"
                role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ==== TAMBAHAN: Ringkasan Kehadiran & Keterlambatan ==== --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow border-left-success h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                    Total Kehadiran
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalKehadiran }} hari</div>
                            </div>
                            <div class="col-auto">
                                <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow border-left-warning h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col me-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                    Total Keterlambatan
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalTerlambat }} hari</div>
                            </div>
                            <div class="col-auto">
                                <i class="fa-solid fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Tombol Absen --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Absen Hari Ini</h6>
            </div>
            <div class="card-body text-center">

                @if (!$user->participant || !$user->participant->registeredDevice || !$user->participant->registeredDevice->is_active)
                    <div class="alert alert-warning mb-0">
                        Anda belum mendaftarkan perangkat. Silakan daftarkan perangkat di
                        <a href="{{ route('pemagang.profile.edit') }}">halaman profil</a> sebelum bisa absen.
                    </div>
                @else
                    <div id="device-status" class="text-muted mb-3">
                        <span class="spinner-border spinner-border-sm"></span> Memeriksa perangkat...
                    </div>

                    <form method="post" action="{{ route('pemagang.attendance.record', ['type' => 'check-in']) }}"
                        id="checkin-form" class="d-inline d-none">
                        @csrf
                        <input type="hidden" name="device_fingerprint" class="fingerprint-field">
                        <button type="submit" class="btn btn-success btn-lg px-4 me-2"
                            {{ $todayAttendance && $todayAttendance->check_in_time ? 'disabled' : '' }}>
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Absen Masuk
                        </button>
                    </form>

                    <form method="post" action="{{ route('pemagang.attendance.record', ['type' => 'check-out']) }}"
                        id="checkout-form" class="d-inline d-none">
                        @csrf
                        <input type="hidden" name="device_fingerprint" class="fingerprint-field">
                        <button type="submit" class="btn btn-danger btn-lg px-4"
                            {{ !$todayAttendance || !$todayAttendance->check_in_time || $todayAttendance->check_out_time ? 'disabled' : '' }}>
                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Absen Pulang
                        </button>
                    </form>

                    @if ($todayAttendance && $todayAttendance->check_in_time)
                        <p class="text-muted mt-3 mb-0">
                            Check-in hari ini:
                            {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->timezone('Asia/Jakarta')->format('H:i:s') }}
                            @if ($todayAttendance->check_out_time)
                                &middot; Check-out:
                                {{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->timezone('Asia/Jakarta')->format('H:i:s') }}
                            @endif
                        </p>
                    @endif
                @endif

            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Kehadiran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $attendance)
                                @php
                                    $jamMasukJakarta = $attendance->check_in_time
                                        ? \Carbon\Carbon::parse($attendance->check_in_time)->timezone('Asia/Jakarta')
                                        : null;
                                    $isTerlambat =
                                        $jamMasukJakarta && $jamMasukJakarta->format('H:i:s') > $batasTepatWaktu;
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('l, d F Y') }}</td>
                                    <td>{{ $jamMasukJakarta ? $jamMasukJakarta->format('H:i:s') : '-' }}</td>
                                    <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->timezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                                    </td>
                                    <td>
                                        @if (!$attendance->check_in_time)
                                            <span class="badge bg-secondary">-</span>
                                        @elseif ($isTerlambat)
                                            <span class="badge bg-warning text-dark">Terlambat</span>
                                        @else
                                            <span class="badge bg-success">Tepat Waktu</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada riwayat absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function generateFingerprint() {
            const raw = [
                navigator.userAgent,
                navigator.language,
                screen.width + 'x' + screen.height,
                screen.colorDepth,
                new Date().getTimezoneOffset()
            ].join('|');

            let hash = 0;
            for (let i = 0; i < raw.length; i++) {
                hash = ((hash << 5) - hash) + raw.charCodeAt(i);
                hash |= 0;
            }
            return hash.toString();
        }

        const deviceStatus = document.getElementById('device-status');
        const checkinForm = document.getElementById('checkin-form');
        const checkoutForm = document.getElementById('checkout-form');

        if (checkinForm && checkoutForm) {
            const fingerprint = generateFingerprint();

            document.querySelectorAll('.fingerprint-field').forEach(function(field) {
                field.value = fingerprint;
            });

            deviceStatus.classList.add('d-none');
            checkinForm.classList.remove('d-none');
            checkoutForm.classList.remove('d-none');
        }
    </script>
@endsection
