<?php

use App\Models\Admin;
use App\Models\Setting;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    Setting::updateOrCreate(['group' => 'seo', 'key' => 'maintenance_mode_enabled'], ['value' => '1']);
    Setting::updateOrCreate(['group' => 'seo', 'key' => 'maintenance_secret'], ['value' => 'letmein123']);
});

it('shows the public a 503 page while maintenance mode is on (TC-SET-007)', function () {
    $this->get(route('home'))->assertStatus(503);
});

it('lets a visitor with the correct secret through, and keeps them in via a bypass cookie (TC-SET-007)', function () {
    $withSecret = $this->get('/?secret=letmein123');
    $withSecret->assertRedirect('/');

    $cookie = collect($withSecret->headers->getCookies())->first(fn ($c) => $c->getName() === 'maintenance_bypass');
    expect($cookie)->not->toBeNull();

    $this->withUnencryptedCookie('maintenance_bypass', $cookie->getValue())
        ->get(route('home'))
        ->assertOk();
});

it('rejects an incorrect secret', function () {
    $this->get('/?secret=wrong-guess')->assertStatus(503);
});

it('never blocks /admin, so staff can always turn maintenance mode back off', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
});

it('always serves sitemap.xml and robots.txt even during maintenance', function () {
    $this->get(route('sitemap'))->assertOk();
    $this->get(route('robots'))->assertOk();
});
