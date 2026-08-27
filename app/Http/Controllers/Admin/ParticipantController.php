<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Participant\AttendancesExport;
use App\Exports\Participant\LogbooksExport;
use App\Exports\ParticipantsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParticipantController;
use App\Http\Requests\UpdateParticipantRequest;
use App\Models\Institute; // Import the Institute model
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ParticipantController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => Participant::count(),
            'laki_laki' => Participant::where('jenis_kelamin', 'L')->count(),
            'perempuan' => Participant::where('jenis_kelamin', 'P')->count(),
        ];

        $years = Participant::whereNotNull('tahun_aktif')
            ->select('tahun_aktif')
            ->distinct()
            ->orderByDesc('tahun_aktif')
            ->pluck('tahun_aktif');

        return view('admin.peserta.index', compact('stats', 'years'));
    }

    public function data(Request $request)
    {
        $query = Participant::query();

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('tahun_aktif')) {
            $query->where('tahun_aktif', (int) $request->tahun_aktif);
        }

        if ($request->filled('searchbox')) {
            $search = $request->searchbox;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisnim', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%")
                    ->orWhere('kontak_peserta', 'like', "%{$search}%")
                    ->orWhere('tahun_aktif', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('actions', function ($p) {
                $buttons = "
                    <div class='flex gap-2'>
                    <a href='".route('admin.peserta.edit', $p->id)."'
                       class='btn btn-sm btn-primary text-white'
                       title='Edit'>
                        <i class='fa-solid fa-pen-to-square'></i> Edit
                    </a>
                    <a href='".route('admin.peserta.show', $p->id)."'
                       class='btn btn-sm btn-info text-white'
                       title='Detail'>
                        <i class='fa-solid fa-eye'></i> Detail
                    </a>";
                if ($p->status !== 'approved') {
                    $buttons .= "
                    <a href='".route('admin.peserta.approve', $p->id)."'
                       class='btn btn-sm btn-success text-white'
                       title='Setujui'>
                        <i class='fa-solid fa-check'></i> Setujui
                    </a>";
                }
                $buttons .= '</div>';

                return $buttons;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function show(Participant $participant): View
    {
        // muat relasi untuk tab logbook & absensi (urut terbaru)
        $participant->load([
            'institute',
            'registeredDevice',
            'logbooks' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'),
            'attendances' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'),
        ]);

        return view('admin.peserta.detail', compact('participant'));
    }

    public function resetDevice(Participant $participant): RedirectResponse
    {
        $device = $participant->registeredDevice;

        if (! $device) {
            return redirect()
                ->route('admin.peserta.show', $participant->id)
                ->with('error', 'Peserta ini belum memiliki perangkat terdaftar.');
        }

        $device->update(['is_active' => false]);

        return redirect()
            ->route('admin.peserta.show', $participant->id)
            ->with('success', "Perangkat {$participant->nama} berhasil direset. Peserta dapat mendaftarkan perangkat baru.");
    }

    public function create()
    {
        $institutes = Institute::all(); // Fetch all institutes

        return view('admin.peserta.create', compact('institutes'));
    }

    public function store(StoreParticipantController $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['permohonan_id'])) {
            $permohonan = \App\Models\Permohonan::find($data['permohonan_id']);
            if ($permohonan) {
                $data['institute_id'] = $permohonan->id_institute;
            }
        }

        $participant = Participant::create($data);

        $internship = \App\Models\Internship::where('id_permohonan', $data['permohonan_id'])->first();
        if ($internship) {
            if (! $internship->participants()->where('participants.id', $participant->id)->exists()) {
                $internship->participants()->attach($participant->id);
            }
        }

        return redirect()
            ->route('admin.peserta.index')
            ->with('sukses', 'Peserta berhasil ditambahkan.');
    }

    public function edit(Participant $participant): View
    {
        $institutes = Institute::all(); // Fetch all institutes

        return view('admin.peserta.edit', compact('participant', 'institutes'));
    }

    public function update(UpdateParticipantRequest $request, Participant $participant): RedirectResponse
    {
        $participant->update($request->validated());

        return redirect()->route('admin.peserta.index')->with('sukses', 'Data berhasil diperbarui');
    }

    // ===== Export list peserta (sudah ada) =====
    public function exportExcel(Request $request)
    {
        $filename = 'peserta_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new ParticipantsExport($request), $filename);
    }

    public function exportPdf(Request $request)
    {
        $q = Participant::query();

        $gender = $request->get('jenis_kelamin');
        $year = $request->get('tahun_aktif');
        $search = $request->get('search');

        if ($gender) {
            $q->where('jenis_kelamin', $gender);
        }
        if ($year) {
            $q->where('tahun_aktif', (int) $year);
        }
        if ($search) {
            $q->where(function ($x) use ($search) {
                $x->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisnim', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%")
                    ->orWhere('kontak_peserta', 'like', "%{$search}%")
                    ->orWhere('tahun_aktif', 'like', "%{$search}%");
            });
        }

        $data = $q->orderBy('nama')->get();

        $subtitle = [];
        if ($gender) {
            $subtitle[] = 'Jenis kelamin: '.($gender === 'L' ? 'Laki-laki' : 'Perempuan');
        }
        if ($year) {
            $subtitle[] = 'Tahun aktif: '.$year;
        }
        if ($search) {
            $subtitle[] = 'Pencarian: "'.$search.'"';
        }
        $subtitle = implode(' · ', $subtitle);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.peserta.pdf', compact('data', 'subtitle'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('peserta_'.now()->format('Ymd_His').'.pdf');
    }

    // ====== NEW: Export riwayat logbook / absensi untuk satu peserta ======
    public function exportLogbookExcel(Participant $participant)
    {
        $filename = 'logbook_'.$participant->id.'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new LogbooksExport($participant), $filename);
    }

    public function exportLogbookPdf(Participant $participant)
    {
        $participant->load(['logbooks' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id')]);

        $pdf = Pdf::loadView('admin.peserta.exports.logbook_pdf', [
            'participant' => $participant,
            'logbooks' => $participant->logbooks,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('logbook_'.$participant->id.'_'.now()->format('Ymd_His').'.pdf');
    }

    public function exportAttendanceExcel(Participant $participant)
    {
        $filename = 'absensi_'.$participant->id.'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new AttendancesExport($participant), $filename);
    }

    public function exportAttendancePdf(\App\Models\Participant $participant)
    {
        $attendances = $participant->attendances()
            ->orderByDesc('date')
            ->get([
                'date',
                'status',
                'note',
                'check_in_time',
                'check_out_time',
                'check_in_ip_address',
                'check_out_ip_address',
            ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.peserta.exports.attendance_pdf', [
            'participant' => $participant,
            'attendances' => $attendances,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('absensi_'.$participant->id.'_'.now()->format('Ymd_His').'.pdf');
    }

    public function approve(Participant $participant, \App\Services\ParticipantService $service): RedirectResponse
    {
        try {
            $password = $service->approveParticipant($participant);
            $message = "Peserta {$participant->nama} berhasil disetujui. Akun telah dibuat. Password sementara: {$password}";

            return redirect()->route('admin.peserta.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.peserta.index')->with('error', $e->getMessage());
        }
    }
}
