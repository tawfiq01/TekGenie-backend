<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\Content\BannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** FR-STF-003, gated by content.manage. */
class BannerController extends Controller
{
    public function __construct(private readonly BannerService $banners) {}

    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => Banner::query()->orderBy('placement')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $this->banners->create($request->validated(), $request->file('image_desktop'), $request->file('image_mobile'));

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', ['banner' => $banner]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $this->banners->update($banner, $request->validated(), $request->file('image_desktop'), $request->file('image_mobile'));

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->banners->destroy($banner);

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }
}
