<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function testCsrfForm(Request $request)
    {
        $sessionId = session()->getId();
        $csrfToken = csrf_token();

        Log::info('GET CSRF Form', [
            'session_id' => $sessionId,
            'csrf_token' => $csrfToken,
            'timestamp' => now(),
        ]);

        return view('debug-csrf-form', [
            'sessionId' => $sessionId,
            'csrfToken' => $csrfToken,
        ]);
    }

    public function testCsrfPost(Request $request)
    {
        $sessionId = session()->getId();
        $receivedToken = $request->input('_token');
        $expectedToken = csrf_token();

        Log::info('POST CSRF Test', [
            'session_id' => $sessionId,
            'received_token' => $receivedToken,
            'expected_token' => $expectedToken,
            'tokens_match' => $receivedToken === $expectedToken,
            'session_data' => session()->all(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'CSRF test received',
            'received_token' => $receivedToken,
            'expected_token' => $expectedToken,
            'match' => $receivedToken === $expectedToken,
            'session_id' => $sessionId,
        ]);
    }
}
