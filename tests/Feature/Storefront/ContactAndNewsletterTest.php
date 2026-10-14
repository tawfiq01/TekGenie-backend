<?php

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Setting;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(SettingsSeeder::class);
});

it('stores a contact message and emails staff (FR-STF-008)', function () {
    Mail::fake();
    Setting::where('group', 'store')->where('key', 'contact_email')->update(['value' => 'staff@example.com']);

    $this->post(route('contact.store'), [
        'name' => 'Rahim Uddin',
        'email' => 'rahim@example.com',
        'subject' => 'Question about delivery',
        'message' => 'Do you deliver to Chattogram?',
        'website' => '',
    ])->assertRedirect(route('contact.show'));

    expect(ContactMessage::where('email', 'rahim@example.com')->exists())->toBeTrue();
    Mail::assertQueued(ContactMessageReceived::class);
});

it('rejects a submission where the honeypot field is filled', function () {
    $this->post(route('contact.store'), [
        'name' => 'Bot', 'email' => 'bot@example.com', 'subject' => 'x', 'message' => 'x',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    expect(ContactMessage::count())->toBe(0);
});

it('subscribes an email to the newsletter (FR-STF-009)', function () {
    $this->post(route('newsletter.store'), ['email' => 'subscriber@example.com'])->assertRedirect();

    $subscriber = NewsletterSubscriber::where('email', 'subscriber@example.com')->first();
    expect($subscriber)->not->toBeNull()
        ->and($subscriber->consented_at)->not->toBeNull();
});

it('shows a published static page and 404s an unpublished one (FR-STF-007)', function () {
    $published = Page::where('slug', 'about')->first() ?? Page::factory()->create(['slug' => 'about', 'is_published' => true]);
    $published->update(['is_published' => true, 'body' => '<p>About our store</p>']);

    $this->get(route('page.show', $published))->assertOk()->assertSee('About our store');

    $draft = Page::factory()->create(['slug' => 'draft-page', 'is_published' => false]);
    $this->get(route('page.show', $draft))->assertNotFound();
});
