<?php

namespace Knowfox\Http\Controllers;

use Knowfox\Models\Concept;
use Knowfox\Models\Share;
use Knowfox\User;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Knowfox\Models\Share  $share
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Concept $concept)
    {
        $shares = [];

        foreach ($request->input('shares') as $data) {
            $share = User::firstOrNew([
                'email' => $data['email']
            ]);
            if (empty($share->name)) {
                $share->name = preg_replace('/\W+/u', ' ', $data['email']);
                $share->save();
            }
            $shares[$share->id] = ['permissions' => $data['pivot']['permissions']];
        }

        $concept->shares()->sync($shares);

        return response()->json(['success' => true]);
    }

    public function emails(Request $request)
    {
        if (!$request->has('q')) {
            return response()->json(['success' => false]);
        }
        $users = User::where('name', 'like', '%' . $request->input('q') . '%')
            ->orWhere('email', 'like', '%' . $request->input('q') . '%')
            ->paginate();

        return response()->json($users);
    }
}
