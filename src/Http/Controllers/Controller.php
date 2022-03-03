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

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Knowfox\Models\EmailLogin;
use Illuminate\Support\Facades\Auth;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function fromToken($token)
    {
        $email_login = EmailLogin::validFromToken($token);
        Auth::login($email_login->user, /*remember*/true);

        return $email_login->user;
    }

    protected function setAuthMiddleWare(Request $request)
    {
        if ($request->hasHeader('authorization')) {
            $this->middleware('auth:sanctum');
        }
        else {
            $this->middleware('web');
        }
    }
}
