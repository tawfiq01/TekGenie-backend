<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Services\Content\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** FR-STF-007, gated by content.manage. */
class PageController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->orderBy('title')->get(),
        ]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->pages->update($page, $request->validated());

        return redirect()->route('admin.pages.index')->with('status', 'Page updated.');
    }
}
