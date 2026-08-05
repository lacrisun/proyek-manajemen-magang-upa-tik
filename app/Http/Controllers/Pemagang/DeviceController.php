<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use App\Models\RegisteredDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'device_fingerprint' => 'required|string',
        ]);

        $user = Auth::user();
        $participant = $user->participant;

        if (!$participant) {
            return back()->withErrors(['device_model' => 'Lengkapi data diri peserta terlebih dahulu.']);
        }

        // cek apakah sudah punya device aktif
        $existing = $participant->registeredDevice;
        if ($existing && $existing->is_active) {
            return back()->withErrors(['device_model' => 'Anda sudah memiliki perangkat terdaftar.']);
        }

        $userAgent = $request->header('User-Agent') ?? '';
        $deviceModel = $this->getPhoneModel($userAgent);

        RegisteredDevice::updateOrCreate(
            ['participant_id' => $participant->id],
            [
                'device_model'       => $deviceModel,
                'device_fingerprint' => $request->input('device_fingerprint'),
                'user_agent'         => $userAgent,
                'registered_at'      => now(),
                'is_active'          => true,
            ]
        );

        return redirect()
            ->route('pemagang.profile.edit') // sesuaikan nama route halaman profil
            ->with('status', 'device-registered');
    }

    private function getPhoneModel(string $userAgent): string
    {
        if (preg_match('/Android.*?;\s*([^;)]+?)\s*Build\//i', $userAgent, $match)) {
            return trim($match[1]);
        }

        if (preg_match('/iPhone/i', $userAgent)) {
            return 'iPhone (model tidak terdeteksi)';
        }

        if (preg_match('/iPad/i', $userAgent)) {
            return 'iPad (model tidak terdeteksi)';
        }

        return 'Tidak diketahui';
    }
}
