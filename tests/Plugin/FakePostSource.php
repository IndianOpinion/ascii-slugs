<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

use IndianOpinion\AsciiSlugs\WordPress\EncodedPost;
use IndianOpinion\AsciiSlugs\WordPress\Encoding;
use IndianOpinion\AsciiSlugs\WordPress\PostSource;

/**
 * PostSource over the in-memory stub posts.
 */
final class FakePostSource implements PostSource
{
    public int $queries = 0;

    public function encodedPosts(array $postTypes, array $statuses, ?array $ids, int $afterId, int $batch): array
    {
        $this->queries++;
        $posts = [];
        $all = \WpStub::$posts;
        ksort($all);
        foreach ($all as $post) {
            if ($post->ID <= $afterId || !Encoding::hasEncodedHighByte($post->post_name)) {
                continue;
            }
            if (!in_array($post->post_type, $postTypes, true) || !in_array($post->post_status, $statuses, true)) {
                continue;
            }
            if ($ids !== null && !in_array($post->ID, $ids, true)) {
                continue;
            }
            $posts[] = new EncodedPost($post->ID, $post->post_name, $post->post_title, $post->post_type, $post->post_status, $post->post_date_gmt);
            if (count($posts) >= $batch) {
                break;
            }
        }

        return $posts;
    }

    public function countEncoded(array $postTypes, array $statuses): int
    {
        return count($this->encodedPosts($postTypes, $statuses, null, 0, PHP_INT_MAX));
    }
}
