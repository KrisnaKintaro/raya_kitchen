<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotTemplate;
use Database\Seeders\BotTemplateSeeder;
use Illuminate\Http\Request;

class WhatsappBotTemplateController extends Controller
{
    //  Mengambil semua daftar template pesan bot WA.
    //  Otomatis menyinkronkan jika ada state baru di BotTemplateSeeder yang belum masuk ke DB,
    //  serta menyematkan 'default_message_text' dari kode (Opsi 1) tanpa membebani DB.
    public function index()
    {
        $defaults = collect(BotTemplateSeeder::getDefaults())->keyBy('session_state');

        // Pastikan seluruh state standar tersedia di tabel bot_templates
        foreach ($defaults as $state => $def) {
            BotTemplate::firstOrCreate(
                ['session_state' => $state],
                [
                    'description'  => $def['description'],
                    'message_text' => $def['message_text'],
                    'is_active'    => true,
                ]
            );
        }

        // Ambil data dari DB dan urutkan sesuai urutan alur percakapan di Seeder
        $dbTemplates = BotTemplate::all()->keyBy('session_state');

        $result = [];
        foreach ($defaults as $state => $def) {
            $row = $dbTemplates->get($state);
            if ($row) {
                $result[] = [
                    'id'                   => $row->id,
                    'session_state'        => $row->session_state,
                    'description'          => $row->description ?? $def['description'],
                    'is_active'            => true,
                    'message_text'         => $row->message_text,
                    'default_message_text' => $def['message_text'],
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }

    // Menyimpan perubahan isi pesan template ke database
    // disertai validasi ketat variabel dinamis {...}
    public function update(Request $request)
    {
        $request->validate([
            'session_state' => 'required|string',
            'message_text'  => 'required|string',
        ]);

        $state = $request->input('session_state');
        $newText = $request->input('message_text');

        $defaults = collect(BotTemplateSeeder::getDefaults())->keyBy('session_state');
        $defaultConfig = $defaults->get($state);

        if (!$defaultConfig) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Skenario template tidak dikenali oleh sistem.'
            ], 404);
        }

        // Ekstrak semua variabel wajib {...} dari blueprint default
        preg_match_all('/\{[a-zA-Z0-9_]+\}/', $defaultConfig['message_text'], $matches);
        $requiredVars = array_unique($matches[0] ?? []);

        $missingVars = [];
        foreach ($requiredVars as $var) {
            if (!str_contains($newText, $var)) {
                $missingVars[] = $var;
            }
        }

        if (!empty($missingVars)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Variabel wajib tidak boleh dihapus: ' . implode(', ', $missingVars)
            ], 422);
        }

        $template = BotTemplate::updateOrCreate(
            ['session_state' => $state],
            [
                'description'  => $defaultConfig['description'],
                'message_text' => $newText,
                'is_active'    => true,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Template pesan berhasil diperbarui!',
            'data'    => [
                'id'                   => $template->id,
                'session_state'        => $template->session_state,
                'description'          => $template->description,
                'is_active'            => true,
                'message_text'         => $template->message_text,
                'default_message_text' => $defaultConfig['message_text'],
            ]
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'session_state' => 'required|string',
        ]);

        $state = $request->input('session_state');
        $defaults = collect(BotTemplateSeeder::getDefaults())->keyBy('session_state');
        $defaultConfig = $defaults->get($state);

        if (!$defaultConfig) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Default template untuk state ini tidak ditemukan.'
            ], 404);
        }

        $template = BotTemplate::updateOrCreate(
            ['session_state' => $state],
            [
                'description'  => $defaultConfig['description'],
                'message_text' => $defaultConfig['message_text'],
                'is_active'    => true,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Template berhasil dikembalikan ke default sistem!',
            'data'    => [
                'id'                   => $template->id,
                'session_state'        => $template->session_state,
                'description'          => $template->description,
                'is_active'            => true,
                'message_text'         => $template->message_text,
                'default_message_text' => $defaultConfig['message_text'],
            ]
        ]);
    }
}