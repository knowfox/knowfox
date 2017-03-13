<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Http\Request;
use Knowfox\User;

class EvernoteController extends Controller
{
    private function log($what, $txt)
    {
        error_log(strftime("[%Y-%m-%d %H:%M:%S] {$what}: {$txt}\n"), 3, "/tmp/evernote.log");
    }

    private function handle(Request $request)
    {
        $status = [];

        if (empty($request->input('userId'))) {
            return [
                'status' => 'error',
                'message' => 'Missing UserId'
            ];
        }

        $user = User::where('evernote_id', $request->input('userId'))->first();
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'No user ' . $request->input('userId'),
            ];
        }

        $notebook_uuid = $request->input('notebookGuid');

    }

    public function webhook(Request $request)
    {
        $resonse = response()
            ->header('Content-type', 'application/json; charset=UTF-8');

        $this->log('webhook', json_encode($request->all()));

        $status = $this->handle($request);

        $this->log($status['status'], $status['message']);

        return $response->json($status);
    }
}
