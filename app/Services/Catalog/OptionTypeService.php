<?php

namespace App\Services\Catalog;

use App\Models\OptionType;
use App\Models\OptionValue;
use App\Services\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

/**
 * Admin-managed option types & values (FR-CAT-004) — e.g. Colour, Size.
 */
class OptionTypeService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array{name: string, display_style: string, sort_order?: int}  $data */
    public function create(array $data): OptionType
    {
        $optionType = OptionType::create([
            'name' => $data['name'],
            'display_style' => $data['display_style'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->audit->log('admin.option-type.created', $optionType, ['name' => $optionType->name]);

        return $optionType;
    }

    /** @param  array{name: string, display_style: string, sort_order?: int}  $data */
    public function update(OptionType $optionType, array $data): OptionType
    {
        $before = $optionType->only(['name', 'display_style', 'sort_order']);

        $optionType->update([
            'name' => $data['name'],
            'display_style' => $data['display_style'],
            'sort_order' => $data['sort_order'] ?? $optionType->sort_order,
        ]);

        $this->audit->log('admin.option-type.updated', $optionType, [
            'before' => $before,
            'after' => $optionType->only(['name', 'display_style', 'sort_order']),
        ]);

        return $optionType;
    }

    /** @param  array{value: string, swatch_hex?: string|null, sort_order?: int}  $data */
    public function addValue(OptionType $optionType, array $data): OptionValue
    {
        $value = $optionType->values()->create([
            'value' => $data['value'],
            'swatch_hex' => $data['swatch_hex'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->audit->log('admin.option-value.created', $value, ['option_type' => $optionType->name, 'value' => $value->value]);

        return $value;
    }

    public function removeValue(OptionValue $value): void
    {
        if ($value->variants()->exists()) {
            throw ValidationException::withMessages([
                'value' => 'This option value is used by one or more product variants and cannot be removed.',
            ]);
        }

        $value->delete();

        $this->audit->log('admin.option-value.deleted', $value);
    }
}
