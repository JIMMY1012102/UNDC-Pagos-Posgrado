<?php

namespace App\Modules\Asistente\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asistente\Exceptions\AssistantUnavailable;
use App\Modules\Asistente\Services\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssistantController extends Controller
{
    public function __invoke(Request $request, AssistantService $assistant): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        try {
            $answer = $assistant->reply($data['message']);
        } catch (AssistantUnavailable $exception) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $exception->getMessage(),
            ], 503)->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'success' => true,
            'data' => ['answer' => $answer],
            'message' => 'Respuesta generada.',
        ])->header('Cache-Control', 'no-store');
    }
}
