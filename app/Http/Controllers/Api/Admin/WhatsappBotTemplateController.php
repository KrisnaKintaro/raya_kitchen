<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotTemplate;
use Database\Seeders\BotTemplateSeeder;
use Illuminate\Http\Request;

class WhatsappBotTemplateController extends Controller
{
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

            preg_match_all('/\{[a-zA-Z0-9_]+\}/', $def['message_text'], $matches);
            $requiredVars = array_unique($matches[0] ?? []);

            preg_match_all('/\{[a-zA-Z0-9_]+\}/', $template->message_text, $dbMatches);
            $dbVars = array_unique($dbMatches[0] ?? []);

            $needsSync = false;

            // Sinkronkan jika ada variabel wajib baru yang belum ada di DB
            foreach ($requiredVars as $var) {
                if (!str_contains($template->message_text, $var)) {
                    $needsSync = true;
                    break;
                }
            }

            // Sinkronkan juga jika di DB masih ada variabel lama yang sudah dihapus dari blueprint Seeder
            foreach ($dbVars as $dbVar) {
                if (!in_array($dbVar, $requiredVars)) {
                    $needsSync = true;
                    break;
                }
            }

            if ($needsSync || $template->description !== $def['description']) {
                $template->update([
                    'description'  => $def['description'],
                    'message_text' => $needsSync ? $def['message_text'] : $template->message_text,
                ]);
            }
        }

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
