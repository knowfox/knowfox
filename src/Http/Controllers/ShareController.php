<?php
/**
 * Knowfox - Personal Knowledge Management
 * Copyright (C) 2017  Olav Schettler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Knowfox\Http\Controllers;

use Knowfox\Models\Concept;
use Knowfox\Models\EmailLogin;
use App\Models\User;
use Knowfox\Jobs\SendInviteMail;
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
        // Only owners are allowed to share
        if ($concept->owner_id != $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not permitted']);
        }

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

        $owner = $request->user();
        $changes = $concept->shares()->sync($shares);
        foreach ($changes['attached'] as $user_id) {
            $user = User::find($user_id);
            $email_login = EmailLogin::createForEmail($user->email);

            $url = route('auth.email-authenticate', [
                'token' => $email_login->token,
                'concept' => $concept->id,
            ]);

            $this->dispatch(new SendInviteMail($owner, $user, $concept, $url));
        }

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
