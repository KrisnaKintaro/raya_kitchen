<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KelolaAkunController extends Controller
{
    /**
     * Normalisasi format nomor WhatsApp (08xxx -> 628xxx) agar konsisten dengan modul Pembeli & Bot WA
     */
    private function formatWhatsapp($number)
    {
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }
        return $clean;
    }

    // =========================================================================
    // 1. MANAJEMEN AKSES ADMIN & STAF
    // =========================================================================

    public function indexAkses()
    {
        $admins = User::whereIn('role', ['admin', 'staf', 'super_admin', 'pemilik_toko'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'whatsapp_number' => $user->whatsapp_number,
                    'role' => $user->role,
                    'profile_photo_url' => $user->profile_photo 
                        ? asset('storage/' . $user->profile_photo) 
                        : null,
                    'created_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $admins
        ]);
    }

    public function storeAkses(Request $request)
    {
        $request->merge([
            'whatsapp_number' => $this->formatWhatsapp($request->whatsapp_number)
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|unique:users,whatsapp_number',
            'role' => 'required|in:admin,staf,super_admin,pemilik_toko',
            'password' => 'required|string|min:6',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'whatsapp_number.unique' => 'Nomor WhatsApp sudah terdaftar di sistem.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            // Simpan ke storage/app/public/akun
            $photoPath = $request->file('profile_photo')->store('akun', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'profile_photo' => $photoPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Akun ' . $user->name . ' berhasil ditambahkan!',
            'data' => $user
        ]);
    }

    public function resetPasswordAkses(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6'
        ], [
            'new_password.min' => 'Password baru minimal 6 karakter.'
        ]);

        $user = User::whereIn('role', ['admin', 'staf', 'super_admin', 'pemilik_toko'])->findOrFail($id);
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password untuk ' . $user->name . ' berhasil diperbarui!'
        ]);
    }

    public function destroyAkses(Request $request, $id)
    {
        $user = User::whereIn('role', ['admin', 'staf', 'super_admin', 'pemilik_toko'])->findOrFail($id);

        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang login.'
            ], 400);
        }

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil dihapus dari sistem.'
        ]);
    }

    // =========================================================================
    // 2. KELOLA DATA PELANGGAN (PEMBELI)
    // =========================================================================

    public function indexPelanggan()
    {
        $pelanggan = User::with(['addresses' => function ($q) {
                $q->orderBy('is_primary', 'desc');
            }])
            ->withCount('orders')
            ->whereNotIn('role', ['admin', 'staf', 'super_admin', 'pemilik_toko'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                $primaryAddr = $user->addresses->first();
                $alamatRingkas = $primaryAddr 
                    ? "{$primaryAddr->street_address}, {$primaryAddr->district}, {$primaryAddr->city}" 
                    : 'Belum menambahkan alamat';

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'whatsapp_number' => $user->whatsapp_number,
                    'profile_photo_url' => $user->profile_photo 
                        ? asset('storage/' . $user->profile_photo) 
                        : null,
                    'orders_count' => $user->orders_count,
                    'primary_address' => $alamatRingkas,
                    'addresses' => $user->addresses,
                    'joined_at' => $user->created_at ? $user->created_at->format('d M Y, H:i') : '-',
                ];
            });

        return response()->json([
            'status' => 'success',
            'total' => $pelanggan->count(),
            'data' => $pelanggan
        ]);
    }
}