<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function store(Request $request, CreateUserStatus $createUserStatus): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'max:500'],
        ]);

        $createUserStatus->handle($request->user(), $request->input('status'));

        return back()->with('message', 'Status updated successfully!');
    }
}
