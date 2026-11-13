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
        $count = $source->countEncoded($this->plugin->postTypes(), ['publish']);

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
                    'value' => (string) $count,
                    'debug' => $count,
                ],
            ],
        ];

        return $info;
    }
}
