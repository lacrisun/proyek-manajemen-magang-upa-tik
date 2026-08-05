@extends('pemagang.layoutspemagang.main')
@section('title', 'Absensi')

@section('content')
    <div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="card shadow" style="max-width: 480px; width: 100%;">
            <div class="card-header bg-primary text-white py-3 text-center">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-clock me-2"></i> Absensi {{ $type === 'check-in' ? 'Masuk' : 'Pulang' }}
                </h5>
            </div>
            <div class="card-body text-center">

                @if (!$user->participant || !$user->participant->registeredDevice || !$user->participant->registeredDevice->is_active)
                    <div class="alert alert-warning">
                        Anda belum mendaftarkan perangkat. Silakan daftarkan perangkat terlebih dahulu di
                        <a href="{{ route('pemagang.profile.edit') }}">halaman profil</a>.
                    </div>
                @else
                    <p class="text-muted mb-4">
                        Perangkat akan diverifikasi secara otomatis. Klik tombol di bawah untuk merekam
                        {{ $type === 'check-in' ? 'jam masuk' : 'jam pulang' }} Anda.
                    </p>

                    <div id="loading-state">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Memverifikasi perangkat...</span>
                        </div>
                        <p class="text-muted">Memverifikasi perangkat...</p>
                    </div>

                    <form method="post" action="{{ $submitUrl }}" id="attendance-form" class="d-none">
                        @csrf
                        <input type="hidden" name="device_fingerprint" id="device_fingerprint">

                        <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                            <i class="fa-solid fa-check me-1"></i>
                            Rekam {{ $type === 'check-in' ? 'Check-In' : 'Check-Out' }}
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>

    <script>
        // Fungsi fingerprint sama persis dengan yang dipakai saat registrasi device,
        // supaya hasil hash-nya konsisten dan bisa dicocokkan di backend.
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

        const fingerprintField = document.getElementById('device_fingerprint');
        const form = document.getElementById('attendance-form');
        const loadingState = document.getElementById('loading-state');

        if (form) {
            fingerprintField.value = generateFingerprint();

            // Sembunyikan loading, tampilkan tombol submit setelah fingerprint siap
            loadingState.classList.add('d-none');
            form.classList.remove('d-none');

            // Opsional: submit otomatis tanpa perlu klik tombol lagi
            // form.submit();
        }
    </script>
@endsection
