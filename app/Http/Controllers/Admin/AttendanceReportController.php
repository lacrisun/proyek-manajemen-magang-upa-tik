<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function daily(Request $request)
    {
        // Jam batas tepat waktu — sesuaikan dengan kebijakan instansi
        $batasTepatWaktu = '08:00:00';

        // Tanggal yang dipilih admin, default hari ini (Jakarta)
        $selectedDate = $request->input('date', Carbon::now('Asia/Jakarta')->toDateString());

        // Validasi format tanggal biar tidak error kalau ada input aneh
        try {
            $carbonDate = Carbon::parse($selectedDate);
        } catch (\Exception $e) {
            $selectedDate = Carbon::now('Asia/Jakarta')->toDateString();
            $carbonDate = Carbon::parse($selectedDate);
        }

        // Hanya ambil peserta yang SUDAH absen (check_in_time tidak null) pada tanggal ini
        $attendances = Attendance::with('participant.institute')
            ->where('date', $selectedDate)
            ->whereNotNull('check_in_time')
            ->get()
            ->map(function ($attendance) use ($batasTepatWaktu) {
                $jamMasukJakarta = Carbon::parse($attendance->check_in_time)->timezone('Asia/Jakarta');
                $jamKeluarJakarta = $attendance->check_out_time
                    ? Carbon::parse($attendance->check_out_time)->timezone('Asia/Jakarta')
                    : null;

                return [
                    'participant' => $attendance->participant,
                    'jam_masuk'   => $jamMasukJakarta->format('H:i:s'),
                    'jam_keluar'  => $jamKeluarJakarta ? $jamKeluarJakarta->format('H:i:s') : null,
                    'status'      => $jamMasukJakarta->format('H:i:s') > $batasTepatWaktu ? 'terlambat' : 'tepat_waktu',
                ];
            });

        $tepatWaktu = $attendances->where('status', 'tepat_waktu')->sortBy('jam_masuk')->values();
        $terlambat  = $attendances->where('status', 'terlambat')->sortBy('jam_masuk')->values();

        return view('admin.attendance-report.daily', [
            'tepatWaktu'   => $tepatWaktu,
            'terlambat'    => $terlambat,
            'selectedDate' => $selectedDate,
            'carbonDate'   => $carbonDate,
        ]);
    }
}
