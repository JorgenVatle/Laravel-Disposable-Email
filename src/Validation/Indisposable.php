<?php

namespace Propaganistas\LaravelDisposableEmail\Validation;

use Illuminate\Support\Facades\Cache as FrameworkCache;
use Exception;

class Indisposable {

    /**
     * The remote JSON source URI.
     *
     * @var string
     */
    protected $sourceUrl = 'https://rawgit.com/andreis/disposable-email-domains/master/domains.json';

    /**
     * Framework cache key.
     *
     * @var string
     */
    protected $cacheKey = 'laravel-disposable-email.cache';

    /**
     * Array of disposable email domains.
     *
     * @var array
     */
    protected static $domains = [];

    /**
     * Indisposable constructor.
     */
    public function __construct() {
        static::$domains = Cache::fetchOrUpdate();
    }

    /**
     * Local domain array parsed and cached for optimal performance.
     *
     * @return array
     */
    public function localDomains() {
        return FrameworkCache::rememberForever($this->cacheKey . 'local', function() {
            return json_decode(file_get_contents(__DIR__.'/../../domains.json'), true);
        });
    }

    /**
     * Remote domain array parsed and cached for optimal performance.
     *
     * @throws Exception
     * @return array
     */
    public function remoteDomains() {
        return FrameworkCache::rememberForever($this->cacheKey, function() {
            $remote = file_get_contents($this->sourceUrl);

            if (! $this->isUsefulJson($remote)) {
                throw new Exception('Couldn\'t reach the remote disposable domain source.');
            }

            return json_decode($remote, true);
        });
    }

    /**
     * Disposable domains list with fallback to the locally stored domain list.
     *
     * @return array
     */
    public function domains() {
        try {
            return $this->remoteDomains();
        } catch (Exception $exception) {
            return $this->localDomains();
        }
    }

    /**
     * Return the remainder of a string after a given value.
     * (Copy of Illuminate\Support's Str::after() method.)
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function stringAfter($subject, $search) {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Checks whether or not the given email address' domain matches one from a disposable email service.
     *
     * @param $email
     * @return bool
     */
    public function isDisposable($email) {
        // Parse the email to its top level domain.
        preg_match("/[^\.\/]+\.[^\.\/]+$/", static::stringAfter($email, '@'), $domain);

        // Just ignore this validator if the value doesn't even resemble an email or domain.
        if (count($domain) === 0) {
            return false;
        }

        return in_array($domain[0], static::$domains);
    }

    /**
     * Check whether the given JSON data is useful.
     *
     * @param string $data
     * @return bool
     */
    private function isUsefulJson($data) {
        $data = json_decode($data, true);

        return json_last_error() === JSON_ERROR_NONE && ! empty($data);
    }
}