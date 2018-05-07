<?php

namespace Propaganistas\LaravelDisposableEmail\Tests\Validation;

use Illuminate\Support\Facades\Cache as FrameworkCache;
use Propaganistas\LaravelDisposableEmail\Tests\TestCase;
use Propaganistas\LaravelDisposableEmail\Facades\Indisposable;

class IndisposableTest extends TestCase {

    /**
     * DisposableEmailCacheTest SetUp
     */
    public function setUp() {
        parent::setUp();
        Indisposable::flushCache();
    }

    /** @test */
    public function non_disposable_email_domains_should_not_be_detected_as_disposable() {
        $this->assertFalse(Indisposable::isDisposable('test@gmail.com'));
    }

    /** @test */
    public function a_commonly_known_disposable_email_provider_should_be_detected_as_disposable() {
        $this->assertTrue(Indisposable::isDisposable('test@yopmail.com'));
    }

    /** @test */
    public function the_indisposable_remote_domains_cache_can_be_flushed() {
        // Loads remote domains and caches them indefinitely.
        Indisposable::remoteDomains();

        $this->assertNotNull(FrameworkCache::get('laravel-disposable-email.cache'));

        Indisposable::flushCache();

        $this->assertNull(FrameworkCache::get('laravel-disposable-email.cache'));
    }

}
