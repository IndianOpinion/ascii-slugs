<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

/**
 * PostSource on the WordPress database. One query per batch, walking the
 * primary key, so memory stays flat on large sites.
 */
final class WpdbPostSource implements PostSource
{
    private \wpdb $db;

    public function __construct(?\wpdb $db = null)
    {
        if ($db === null) {
            global $wpdb;
            $db = $wpdb;
        }
        $this->db = $db;
    }

    public function encodedPosts(array $postTypes, array $statuses, ?array $ids, int $afterId, int $batch): array
    {
        if ($postTypes === [] || $statuses === [] || $ids === []) {
            return [];
        }
        [$where, $args] = $this->where($postTypes, $statuses, $ids);
        $sql = 'SELECT ID, post_name, post_title, post_type, post_status, post_date_gmt FROM %i'
            . ' WHERE ID > %d AND ' . $where . ' ORDER BY ID ASC LIMIT %d';
        $query = $this->db->prepare($sql, array_merge([$this->db->posts, $afterId], $args, [max(1, $batch)]));
        $rows = $query === null ? [] : $this->db->get_results($query, ARRAY_A);

        $posts = [];
        // get_results() returns null on a database error.
        foreach ((array) $rows as $row) {
            $slug = (string) ($row['post_name'] ?? '');
            if (!Encoding::hasEncodedHighByte($slug)) {
                continue;
            }
            $posts[] = new EncodedPost(
                (int) ($row['ID'] ?? 0),
                $slug,
                (string) ($row['post_title'] ?? ''),
                (string) ($row['post_type'] ?? ''),
                (string) ($row['post_status'] ?? ''),
                (string) ($row['post_date_gmt'] ?? ''),
            );
        }

        return $posts;
    }

    public function countEncoded(array $postTypes, array $statuses): int
    {
        if ($postTypes === [] || $statuses === []) {
            return 0;
        }
        [$where, $args] = $this->where($postTypes, $statuses, null);
        $query = $this->db->prepare('SELECT COUNT(*) FROM %i WHERE ' . $where, array_merge([$this->db->posts], $args));

        return $query === null ? 0 : (int) $this->db->get_var($query);
    }

    public function countPosts(array $postTypes, array $statuses, int $limit): int
    {
        if ($postTypes === [] || $statuses === []) {
            return 0;
        }
        // post_type and post_status are the head of core's type_status_date
        // index, so this reads the index only and stops at $limit + 1.
        $sql = 'SELECT COUNT(*) FROM (SELECT 1 FROM %i'
            . ' WHERE post_type IN (' . self::placeholders('%s', count($postTypes)) . ')'
            . ' AND post_status IN (' . self::placeholders('%s', count($statuses)) . ')'
            . ' LIMIT %d) AS capped';
        $query = $this->db->prepare($sql, array_merge([$this->db->posts], $postTypes, $statuses, [max(0, $limit) + 1]));

        return $query === null ? 0 : (int) $this->db->get_var($query);
    }

    /**
     * @param list<string> $postTypes
     * @param list<string> $statuses
     * @param list<int>|null $ids
     *
     * @return array{0: literal-string, 1: list<int|string>}
     */
    private function where(array $postTypes, array $statuses, ?array $ids): array
    {
        $where = 'post_name REGEXP %s'
            . ' AND post_type IN (' . self::placeholders('%s', count($postTypes)) . ')'
            . ' AND post_status IN (' . self::placeholders('%s', count($statuses)) . ')';
        $args = array_merge([Encoding::HIGH_BYTE_SQL], $postTypes, $statuses);
        if ($ids !== null) {
            $where .= ' AND ID IN (' . self::placeholders('%d', count($ids)) . ')';
            $args = array_merge($args, $ids);
        }

        return [$where, $args];
    }

    /**
     * "%s, %s, %s" for $count placeholders.
     *
     * @param '%s'|'%d' $placeholder
     *
     * @return literal-string
     */
    private static function placeholders(string $placeholder, int $count): string
    {
        $list = $placeholder;
        for ($i = 1; $i < $count; $i++) {
            $list .= ', ' . $placeholder;
        }

        return $list;
    }
}
