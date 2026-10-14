<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOptionTypeRequest;
use App\Http\Requests\Admin\UpdateOptionTypeRequest;
use App\Models\OptionType;
use App\Services\Catalog\OptionTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OptionTypeController extends Controller
{
    public function __construct(private readonly OptionTypeService $optionTypes) {}

    public function index(): View
    {
        return view('admin.option-types.index', [
            'optionTypes' => OptionType::query()->withCount('values')->orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.option-types.create');
    }

    public function store(StoreOptionTypeRequest $request): RedirectResponse
    {
        $optionType = $this->optionTypes->create($request->validated());

        return redirect()->route('admin.option-types.edit', $optionType)->with('status', 'Option type created — add its values below.');
    }

    public function edit(OptionType $optionType): View
    {
        return view('admin.option-types.edit', [
            'optionType' => $optionType->load('values'),
        ]);
    }

    public function update(UpdateOptionTypeRequest $request, OptionType $optionType): RedirectResponse
    {
        $this->optionTypes->update($optionType, $request->validated());

        return redirect()->route('admin.option-types.index')->with('status', 'Option type updated.');
    }
}
