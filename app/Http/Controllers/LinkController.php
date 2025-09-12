<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddLink;
use App\Actions\AddLinkNote;
use App\Actions\CreateStatusWithLink;
use App\Actions\UpdateUserLink;
use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class LinkController extends Controller
{
    /**
     * Add a new link to user's collection and create status update.
     */
    public function store(Request $request, CreateStatusWithLink $createStatusWithLink): RedirectResponse
    {
        $request->validate([
            'url' => 'required|url',
            'category' => ['required', new Enum(LinkCategory::class)],
        ]);
        try {
            $result = $createStatusWithLink->handle(
                url: $request->input('url'),
                thoughts: null,
                user: $request->user(),
                categoryHint: $request->input('category')
            );

            $message = $result['already_bookmarked']
                ? 'Link was already in your collection! Status shared.'
                : 'Link added to your collection and status shared!';

            return redirect()->back()->with('success', $message);

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Add a note to user's bookmarked link.
     */
    public function storeNote(Request $request, AddLinkNote $addLinkNote): RedirectResponse
    {
        $request->validate([
            'link_id' => 'required|integer|exists:links,id',
            'note' => 'required|string|max:1000',
        ]);

        try {
            $addLinkNote->handle(
                linkId: (int) $request->input('link_id'),
                note: $request->input('note'),
                user: $request->user()
            );

            return redirect()->back()->with('success', 'Note added!');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Update status/category for user's link.
     */
    public function update(Request $request, int $userLinkId, UpdateUserLink $updateUserLink): RedirectResponse
    {
        $request->validate([
            'status' => ['nullable', new Enum(LinkStatus::class)],
            'category' => ['nullable', new Enum(LinkCategory::class)],
        ]);

        try {
            $updateUserLink->handle(
                userLinkId: $userLinkId,
                user: $request->user(),
                status: $request->input('status'),
                category: $request->input('category')
            );

            return redirect()->back()->with('success', 'Link updated!');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Create status with link - for the inline form.
     */
    public function storeStatusWithLink(Request $request, CreateStatusWithLink $createStatusWithLink): RedirectResponse
    {
        $request->validate([
            'url' => 'required|url|max:2048',
            'thoughts' => 'nullable|string|max:200',
        ]);

        try {
            $result = $createStatusWithLink->handle(
                url: $request->input('url'),
                thoughts: $request->input('thoughts'),
                user: $request->user()
            );

            $message = $result['already_bookmarked']
                ? 'Status shared! Link was already in your collection.'
                : 'Status shared and link added to your collection!';

            return redirect()->back()->with('success', $message);

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Bookmark an existing link for the current user.
     */
    public function bookmark(Request $request, int $linkId, AddLink $addLink): RedirectResponse
    {
        try {
            // Find the link by ID and get its URL
            $link = \App\Models\Link::findOrFail($linkId);

            $result = $addLink->handle(
                url: $link->url,
                user: $request->user(),
                categoryHint: 'read'
            );

            $message = $result['already_bookmarked']
                ? 'Link was already in your collection!'
                : 'Link added to your collection!';

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to bookmark link.');
        }
    }
}
