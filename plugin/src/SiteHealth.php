<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

use IndianOpinion\AsciiSlugs\Transliterator;

/**
 * A section on the Site Health Info screen: intl availability and the
 * number of percent-encoded post slugs still stored.
 */
final class SiteHealth
{
    /**
     * The encoded-slug count reads every published row of the covered post
     * types (REGEXP cannot use an index). Above this many rows it is not
     * run, so the Info screen stays fast on large sites.
     */
    public const SCAN_LIMIT = 100000;

    private ?PostSource $source;

    public function __construct(private readonly Plugin $plugin, ?PostSource $source = null)
    {
        $this->source = $source;
    }

    public function register(): void
    {
        add_filter('debug_information', [$this, 'addSection']);
    }

    /**
     * @param mixed $info The Site Health info array.
     *
     * @return mixed
     */
    public function addSection(mixed $info): mixed
    {
        if (!is_array($info)) {
            return $info;
        }

        $intl = Transliterator::intlAvailable();
        $source = $this->source ?? new WpdbPostSource();
        $types = $this->plugin->postTypes();
        $limit = $this->scanLimit();
        if ($source->countPosts($types, ['publish'], $limit) > $limit) {
            $count = null;
            $countValue = sprintf(
                /* translators: %d: number of published posts. */
                __('Not counted on this screen: more than %d published posts. Run "wp ascii-slugs audit --format=count".', 'ascii-slugs'),
                $limit
            );
        } else {
            $count = $source->countEncoded($types, ['publish']);
            $countValue = (string) $count;
        }

        $info['ascii-slugs'] = [
            'label' => __('ASCII Slugs', 'ascii-slugs'),
            'description' => __('Readable ASCII slugs for titles in non-Latin scripts.', 'ascii-slugs'),
            'fields' => [
                'version' => [
                    'label' => __('Version', 'ascii-slugs'),
                    'value' => Plugin::VERSION,
                ],
                'enabled' => [
                    'label' => __('Enabled', 'ascii-slugs'),
                    'value' => $this->plugin->isEnabled() ? __('Yes', 'ascii-slugs') : __('No', 'ascii-slugs'),
                    'debug' => $this->plugin->isEnabled() ? 'yes' : 'no',
                ],
                'intl' => [
                    'label' => __('intl extension', 'ascii-slugs'),
                    'value' => $intl
                        ? sprintf(__('Available (ICU %s)', 'ascii-slugs'), (string) Transliterator::icuVersion())
                        : __('Not available: scripts without a built-in table get the hashed slug', 'ascii-slugs'),
                    'debug' => $intl ? 'yes' : 'no',
                ],
                'encoded_slugs' => [
                    'label' => __('Published posts with percent-encoded slugs', 'ascii-slugs'),
                    'value' => $countValue,
                    'debug' => $count ?? 'not counted',
                ],
            ],
        ];

        return $info;
    }

    /**
     * Rows the count may read, from `ascii_slugs_site_health_scan_limit`.
     */
    private function scanLimit(): int
    {
        $limit = apply_filters('ascii_slugs_site_health_scan_limit', self::SCAN_LIMIT);
        if (!is_int($limit) || $limit < 0) {
            $this->plugin->logger()->notice('ascii_slugs_site_health_scan_limit returned a non-integer; ignored');

            return self::SCAN_LIMIT;
        }

        return $limit;
    }
}
