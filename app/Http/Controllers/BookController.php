<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Http\Request;
use Knowfox\Models\Concept;
use Knowfox\Models\EmailLogin;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    private function fromToken($token)
    {
        $email_login = EmailLogin::validFromToken($token);
        Auth::login($email_login->user, /*remember*/true);

        return $email_login->user;
    }

    public function find(Request $request)
    {
        try {
            $user = $this->fromToken($request->input('token'));
        }
        catch (\Exception $e) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'No user for token "' . $request->input('token') . '"',
                ], 404);
        }

        $root = Concept::whereIsRoot()
            ->where('title', 'Books')
            ->where('owner_id', $user->id)
            ->first();

        if (!$root) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'No toplevel "Books"'
                ], 404);
        }

        $concepts = Concept::where('parent_id', $root->id)
            ->where('title', $request->title)
            ->where('owner_id', $user->id);

        if ($concepts->count() == 0) {
            return response()->json([
                'uuid' => null,
                'csrf_token' => csrf_token(),
                'count' => 0,
            ]);
        }
        else {

            $count = 0;
            foreach ($concepts->get() as $concept) {

                if ($request->input('author')
                    && (
                        empty($concept->config->author)
                        || $concept->config->author != $request->author
                    )
                ) {

                    continue;
                }

                if ($request->input('year')
                    && (
                        empty($concept->config->year)
                        || $concept->config->year != $request->year
                    )
                ) {

                    continue;
                }

                $count++;
            }

            if ($count > 1) {
                return response()
                    ->json([
                        'status' => 'error',
                        'message' => 'More than one book found',
                    ], 404);
            }
            else {
                return response()->json([
                    'uuid' => $concept->uuid,
                    'csrf_token' => csrf_token(),
                    'count' => $count,
                ]);
            }
        }
    }

    public function save(Request $request)
    {
        $user = $this->fromToken($request->input('token'));
        $root = Concept::whereIsRoot()->where('title', 'Books')->firstOrFail();

        $uuid = $request->input('uuid');
        if ($uuid) {
            $concept = Concept::where('uuid', $uuid)
                ->where('owner_id', $user->id)
                ->firstOrFail();
        }
        else {
            $concept = new Concept();
            $concept->fill([
                'title' => $request->title,
                'owner_id' => $user->id,
                'parent_id' => $root->id,
            ]);
        }

        $concept->config = [
            'image' => $request->cover,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'filename' => $request->filename,
            'path' => $request->path,
            'type' => $request->type,
            'format' => $request->format,
        ];

        $concept->type = 'ebook';
        $concept->created_at = $request->created_at;
        $concept->updated_at = $request->updated_at;

        $concept->save();

        if (!empty($request->tags)) {
            $concept->retag(preg_split('/\s+/', $request->tags));
        }

        return response()->json([
            'url' => route('concept.show', [$concept]),
            'status' => $uuid ? 'updated' : 'created',
            'value' => $concept->getAttributes(),
        ]);
    }

    public function reader(Request $request, Concept $concept)
    {
        return view('concept.reader', [
            'page_title' => $concept->title,
            'concept' => $concept,
            'is_owner' => $concept->owner_id == $request->user()->id,
            'can_update' => $request->user()->can('update', $concept),
        ]);
    }
}
