<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $participant = $user->participant;

        // Jam masuk standar — sesuaikan dengan kebijakan instansi
        $batasTepatWaktu = '08:00:00';

        $attendances = $participant
            ? Attendance::where('participant_id', $participant->id)
            ->orderBy('date', 'desc')
            ->paginate(10)
            : Attendance::query()->whereRaw('1 = 0')->paginate(10);

        $todayAttendance = $participant
            ? Attendance::where('participant_id', $participant->id)
            ->where('date', Carbon::today()->toDateString())
            ->first()
            : null;

        // Hitung total kehadiran & keterlambatan (dari SELURUH data, bukan cuma yang di halaman ini)
        $totalKehadiran = 0;
        $totalTerlambat = 0;

        if ($participant) {
            $allAttendances = Attendance::where('participant_id', $participant->id)
                ->whereNotNull('check_in_time')
                ->get();

            $totalKehadiran = $allAttendances->count();

            $totalTerlambat = $allAttendances->filter(function ($attendance) use ($batasTepatWaktu) {
                $jamMasuk = Carbon::parse($attendance->check_in_time)->timezone('Asia/Jakarta')->format('H:i:s');
                return $jamMasuk > $batasTepatWaktu;
            })->count();
        }

        return view('pemagang.attendance.index', compact(
            'user',
            'attendances',
            'todayAttendance',
            'totalKehadiran',
            'totalTerlambat',
            'batasTepatWaktu'
        ));
    }



    public function show(Request $request)
    {
        // 1. Validasi URL yang ditandatangani (sama seperti di record())
        if (! $request->hasValidSignature()) {
            $redirectUrl = route('dashboard');
            $message = 'URL absensi tidak valid atau telah kedaluwarsa. Silakan coba lagi dengan memindai QR code terbaru.';

            return response()->view('errors.invalid_absen', [
                'message' => $message,
                'redirectUrl' => $redirectUrl,
            ], 403);
        }

        $type = $request->query('type'); // 'check-in' atau 'check-out'

        if (! in_array($type, ['check-in', 'check-out'])) {
            return view('pemagang.attendance.result', [
                'isError' => true,
                'message' => 'Tipe absensi tidak dikenal.',
            ]);
        }

        $user = Auth::user();

        // 2. Cek IP di sini juga, supaya user langsung tahu sebelum coba submit
        $clientIp = $request->ip();
        $isIpAllowed = Str::startsWith($clientIp, '103.') || Str::startsWith($clientIp, '172.') || $clientIp === '127.0.0.1';

        if (! $isIpAllowed) {
            return view('pemagang.attendance.result', [
                'isError' => true,
                'message' => "Anda harus terhubung ke jaringan WiFi kampus yang diizinkan untuk melakukan absensi. (IP Anda: {$clientIp})",
            ]);
        }

        // 3. Buat submitUrl dengan signature baru, khusus untuk endpoint POST record()
        //    (bukan pakai signature dari QR yang sama, karena method HTTP-nya beda)
        $submitUrl = URL::temporarySignedRoute(
            'pemagang.attendance.record',
            now()->addMinutes(5), // signature submit berlaku singkat, cukup untuk proses absen
            ['type' => $type]
        );

        return view('pemagang.attendance.show', [
            'user' => $user,
            'type' => $type,
            'submitUrl' => $submitUrl,
        ]);
    }

    // public function record(Request $request)
    // {
    //     // 1. Validasi URL yang ditandatangani
    //     if (! $request->hasValidSignature()) {
    //         $redirectUrl = route('dashboard');
    //         $message = 'URL absensi tidak valid atau telah kedaluwarsa. Silakan coba lagi dengan memindai QR code terbaru.';

    //         return response()->view('errors.invalid_absen', [
    //             'message' => $message,
    //             'redirectUrl' => $redirectUrl,
    //         ], 403);
    //     }

    //     $clientIp = $request->ip();
    //     // Cek IP lokal/private
    //     $isIpAllowed = Str::startsWith($clientIp, '103.') || Str::startsWith($clientIp, '172.') || $clientIp === '127.0.0.1';

    //     // 2. Validasi IP
    //     if (! $isIpAllowed) {
    //         $message = "Anda harus terhubung ke jaringan WiFi kampus yang diizinkan untuk melakukan absensi. (IP Anda: {$clientIp})";
    //         return view('pemagang.attendance.result', ['isError' => true, 'message' => $message]);
    //     }

    //     $participant = Auth::user()->participant;

    //     if (! $participant) {
    //         return view('pemagang.attendance.result', [
    //             'isError' => true,
    //             'message' => 'Data diri Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.',
    //         ]);
    //     }

    //     // 3. Validasi Device Terdaftar
    //     $registeredDevice = $participant->registeredDevice;

    //     if (! $registeredDevice || ! $registeredDevice->is_active) {
    //         return view('pemagang.attendance.result', [
    //             'isError' => true,
    //             'message' => 'Anda belum mendaftarkan perangkat. Silakan daftarkan perangkat di halaman profil terlebih dahulu.',
    //         ]);
    //     }

    //     $currentFingerprint = $request->input('device_fingerprint');

    //     if (! $currentFingerprint || $currentFingerprint !== $registeredDevice->device_fingerprint) {
    //         return view('pemagang.attendance.result', [
    //             'isError' => true,
    //             'message' => 'Absensi hanya dapat dilakukan menggunakan perangkat yang terdaftar (' . ($registeredDevice->device_model ?? 'perangkat Anda') . '). Hubungi admin jika Anda mengganti perangkat.',
    //         ]);
    //     }

    //     // update waktu pemakaian terakhir device
    //     $registeredDevice->update(['last_used_at' => now()]);

    //     $today = Carbon::today()->toDateString();

    //     // Kita ambil waktu sekarang (biasanya UTC di server)
    //     $now = now();

    //     // Siapkan variabel waktu Jakarta HANYA untuk tampilan pesan (feedback user)
    //     $nowJakarta = $now->clone()->setTimezone('Asia/Jakarta');

    //     $type = $request->query('type'); // 'check-in' atau 'check-out'

    //     // Dapatkan/siapkan record absensi hari ini
    //     $attendance = Attendance::firstOrNew([
    //         'participant_id' => $participant->id,
    //         'date' => $today,
    //     ]);

    //     // --- LOGIKA CHECK-IN ---
    //     if ($type === 'check-in') {
    //         if ($attendance->check_in_time) {
    //             return view('pemagang.attendance.result', [
    //                 'isError' => true,
    //                 'message' => 'Anda sudah melakukan check-in hari ini.',
    //             ]);
    //         }

    //         $attendance->check_in_time = $now;
    //         $attendance->check_in_ip_address = $clientIp;
    //         $attendance->save();

    //         return view('pemagang.attendance.result', [
    //             'message' => 'Check-in berhasil direkam pada pukul ' . $nowJakarta->format('H:i:s'),
    //         ]);
    //     }

    //     // --- LOGIKA CHECK-OUT ---
    //     if ($type === 'check-out') {
    //         if (is_null($attendance->check_in_time)) {
    //             return view('pemagang.attendance.result', [
    //                 'isError' => true,
    //                 'message' => 'Anda harus melakukan check-in terlebih dahulu sebelum check-out.',
    //             ]);
    //         }

    //         if ($attendance->check_out_time) {
    //             return view('pemagang.attendance.result', [
    //                 'isError' => true,
    //                 'message' => 'Anda sudah melakukan check-out hari ini.',
    //             ]);
    //         }

    //         $attendance->check_out_time = $now;
    //         $attendance->check_out_ip_address = $clientIp;
    //         $attendance->save();

    //         return view('pemagang.attendance.result', [
    //             'message' => 'Check-out berhasil direkam pada pukul ' . $nowJakarta->format('H:i:s'),
    //         ]);
    //     }

    //     return view('pemagang.attendance.result', [
    //         'isError' => true,
    //         'message' => 'Tipe absensi tidak dikenal.',
    //     ]);
    // }


    public function record(Request $request, string $type)
    {
        $clientIp = $request->ip();
        $isIpAllowed = $clientIp === '103.10.169.26';

        if (! $isIpAllowed) {
            return back()->with([
                'status'  => "Anda harus terhubung ke jaringan WiFi kampus yang diizinkan untuk melakukan absensi. (IP Anda: {$clientIp})",
                'isError' => true,
            ]);
        }

        $participant = Auth::user()->participant;

        if (! $participant) {
            return back()->with([
                'status'  => 'Data diri Anda belum lengkap. Silakan lengkapi profil terlebih dahulu.',
                'isError' => true,
            ]);
        }

        $registeredDevice = $participant->registeredDevice;

        if (! $registeredDevice || ! $registeredDevice->is_active) {
            return back()->with([
                'status'  => 'Anda belum mendaftarkan perangkat. Silakan daftarkan perangkat di halaman profil terlebih dahulu.',
                'isError' => true,
            ]);
        }

        $currentFingerprint = $request->input('device_fingerprint');

        if (! $currentFingerprint || $currentFingerprint !== $registeredDevice->device_fingerprint) {
            return back()->with([
                'status'  => 'Absensi hanya dapat dilakukan menggunakan perangkat yang terdaftar (' . ($registeredDevice->device_model ?? 'perangkat Anda') . '). Hubungi admin jika Anda mengganti perangkat.',
                'isError' => true,
            ]);
        }

        $registeredDevice->update(['last_used_at' => now()]);

        $today = Carbon::today()->toDateString();
        $now = now();
        $nowJakarta = $now->clone()->setTimezone('Asia/Jakarta');

        $attendance = Attendance::firstOrNew([
            'participant_id' => $participant->id,
            'date' => $today,
        ]);

        if ($type === 'check-in') {
            if ($attendance->check_in_time) {
                return back()->with(['status' => 'Anda sudah melakukan check-in hari ini.', 'isError' => true]);
            }

            $attendance->check_in_time = $now;
            $attendance->check_in_ip_address = $clientIp;
            $attendance->save();

            return back()->with(['status' => 'Check-in berhasil direkam pada pukul ' . $nowJakarta->format('H:i:s')]);
        }

        if ($type === 'check-out') {
            if (is_null($attendance->check_in_time)) {
                return back()->with(['status' => 'Anda harus melakukan check-in terlebih dahulu.', 'isError' => true]);
            }

            if ($attendance->check_out_time) {
                return back()->with(['status' => 'Anda sudah melakukan check-out hari ini.', 'isError' => true]);
            }

            $attendance->check_out_time = $now;
            $attendance->check_out_ip_address = $clientIp;
            $attendance->save();

            return back()->with(['status' => 'Check-out berhasil direkam pada pukul ' . $nowJakarta->format('H:i:s')]);
        }

        return back()->with(['status' => 'Tipe absensi tidak dikenal.', 'isError' => true]);
    }
}
