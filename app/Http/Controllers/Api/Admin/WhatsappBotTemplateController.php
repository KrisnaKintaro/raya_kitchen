<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotTemplate;
use Database\Seeders\BotTemplateSeeder;
use Illuminate\Http\Request;

class WhatsappBotTemplateController extends Controller
{
    // Mengambil semua daftar template pesan bot WA.
    // Otomatis menyinkronkan jika ada state baru atau variabel baru (seperti {link_gambar})
    // di BotTemplateSeeder yang belum masuk ke DB.
    public function index()
    {
        $defaults = collect(BotTemplateSeeder::getDefaults())->keyBy('session_state');

        foreach ($defaults as $state => $def) {
            $template = BotTemplate::firstOrCreate(
                ['session_state' => $state],
                [
                    'description'  => $def['description'],
                    'message_text' => $def['message_text'],
                    'is_active'    => true,
                ]
            );

            // Auto-sync: Jika di Seeder ada penambahan placeholder baru (misal {link_gambar} pada detail_varian)
            // yang belum ada di row DB lama, otomatis update ke blueprint terbaru atau sisipkan variabelnya
            preg_match_all('/\{[a-zA-Z0-9_]+\}/', $def['message_text'], $matches);
            $requiredVars = array_unique($matches[0] ?? []);

            $needsSync = false;
            foreach ($requiredVars as $var) {
                if (!str_contains($template->message_text, $var)) {
                    $needsSync = true;
                    break;
                }
            }

            if ($needsSync) {
                $template->update([
                    'description'  => $def['description'],
                    'message_text' => $def['message_text'],
                ]);
            }
        }

        // Ambil data dari DB dan urutkan sesuai urutan alur percakapan di Seeder
        $dbTemplates = BotTemplate::all()->keyBy('session_state');
        $result = [];

        foreach ($defaults as $state => $def) {
            $row = $dbTemplates->get($state);
            if ($row) {
                preg_match_all('/\{[a-zA-Z0-9_]+\}/', $def['message_text'], $matches);
                $requiredVars = array_values(array_unique($matches[0] ?? []));

                $result[] = [
                    'id'                   => $row->id,
                    'session_state'        => $row->session_state,
                    'description'          => $row->description ?? $def['description'],
                    'is_active'            => true,
                    'message_text'         => $row->message_text,
                    'default_message_text' => $def['message_text'],
                    'required_variables'   => $requiredVars,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }

    // Menyimpan perubahan isi pesan template ke database
    // disertai validasi ketat variabel dinamis {...} (termasuk {link_gambar})
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
        $requiredVars = array_values(array_unique($matches[0] ?? []));

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
                'required_variables'   => $requiredVars,
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

        preg_match_all('/\{[a-zA-Z0-9_]+\}/', $defaultConfig['message_text'], $matches);
        $requiredVars = array_values(array_unique($matches[0] ?? []));

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
                'required_variables'   => $requiredVars,
            ]
        ]);
    }
}
