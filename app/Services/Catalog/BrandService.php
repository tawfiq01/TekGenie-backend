<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array<string, mixed>  $data */
    public function create(array $data, ?UploadedFile $logo = null): Brand
    {
        $brand = DB::transaction(function () use ($data, $logo) {
            return Brand::create([
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name'], null),
                'logo_path' => $logo ? $this->storeLogo($logo) : null,
                'is_active' => true,
            ]);
        });

        $this->audit->log('admin.brand.created', $brand, ['name' => $brand->name, 'slug' => $brand->slug]);

        return $brand;
    }

    /** @param  array<string, mixed>  $data */
    public function update(Brand $brand, array $data, ?UploadedFile $logo = null): Brand
    {
        $before = $brand->only(['name', 'slug', 'is_active']);

        DB::transaction(function () use ($brand, $data, $logo) {
            $brand->forceFill([
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name'], $brand),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $brand->is_active,
            ]);

            if ($logo) {
                $brand->logo_path = $this->storeLogo($logo);
            }

            $brand->save();
        });

        $this->audit->log('admin.brand.updated', $brand, [
            'before' => $before,
            'after' => $brand->only(['name', 'slug', 'is_active']),
        ]);

        return $brand;
    }

    public function destroy(Brand $brand): void
    {
        if ($brand->products()->exists()) {
            throw ValidationException::withMessages([
                'brand' => 'A brand with products cannot be deleted — deactivate it instead.',
            ]);
        }

        $brand->delete();

        $this->audit->log('admin.brand.deleted', $brand);
    }

    private function resolveSlug(?string $slug, string $name, ?Brand $self): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 2;

        while (
            Brand::query()
                ->where('slug', $candidate)
                ->when($self, fn ($q) => $q->where('id', '!=', $self->id))
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private function storeLogo(UploadedFile $logo): string
    {
        return Storage::disk('public')->putFile('brands', $logo);
    }
}
