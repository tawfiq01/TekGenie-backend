<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOptionValueRequest;
use App\Models\OptionType;
use App\Models\OptionValue;
use App\Services\Catalog\OptionTypeService;
use Illuminate\Http\RedirectResponse;

class OptionValueController extends Controller
{
    public function __construct(private readonly OptionTypeService $optionTypes) {}

    public function store(StoreOptionValueRequest $request, OptionType $optionType): RedirectResponse
    {
        $this->optionTypes->addValue($optionType, $request->validated());

        return redirect()->route('admin.option-types.edit', $optionType)->with('status', 'Value added.');
    }

    public function destroy(OptionValue $optionValue): RedirectResponse
    {
        $optionType = $optionValue->optionType;

        $this->optionTypes->removeValue($optionValue);

        return redirect()->route('admin.option-types.edit', $optionType)->with('status', 'Value removed.');
    }
}
