<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateHomeSectionsRequest;
use App\Models\HomeSection;
use App\Services\Content\HomeSectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** FR-STF-001, gated by content.manage. */
class HomeSectionController extends Controller
{
    public function __construct(private readonly HomeSectionService $sections) {}

    public function index(): View
    {
        return view('admin.home-sections.index', [
            'sections' => HomeSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateHomeSectionsRequest $request): RedirectResponse
    {
        $this->sections->updateAll($request->validated()['sections']);

        return redirect()->route('admin.home-sections.index')->with('status', 'Home page sections updated.');
    }
}
