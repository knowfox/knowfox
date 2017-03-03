<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Http\Request;
use Knowfox\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @todo Adapt from Chosenreich
     * @param $what
     * @param $email
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($what, $email)
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($what == 'all') {
            $user->email_upcoming = 0;
            $user->email_comment = 0;
            $user->email_story = 0;
            $user->email_recipient = 0;
            $user->email_newsletter = 0;
            $user->email_promo = 0;
            $user->save();

            return redirect()->back()
                ->with('message', "Du erhälst keine E-Mails mehr von uns");
        }
        else {
            if (in_array($what, [
                'upcoming',
                'comment',
                'story',
                'recipient',
                'newsletter',
                'promo'
            ])) {
                $field = 'email_' . $what;
                $user->{$field} = 0;
                $user->save();

                return redirect()->back()
                    ->with('message', "Du erhälst keine E-Mails zu ' 
                    . ucfirst($what) . ' mehr von uns");
            }
            else {
                return redirect()->back()
                    ->with('message', 'Zu diesem Thema versenden wir keine E-Mails');
            }
        }
    }

}
