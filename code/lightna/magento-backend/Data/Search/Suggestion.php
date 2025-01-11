<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Search;

use Lightna\Engine\Data\DataA;

/**
 * @method string title(string $escapeMethod = null)
 * @method string numResults(string $escapeMethod = null)
 * @method string url(string $escapeMethod = null)
 * @method string query(string $escapeMethod = null)
 */
class Suggestion extends DataA
{
    public string $title;
    public int $numResults;
    public string $url;
    public string $query;

    public function term(): string
    {
        return $this->highlightQuery($this->title, $this->query);
    }

    protected function highlightQuery(string $text, string $query): string
    {
        $query = preg_quote(escape($query), '/');

        return preg_replace('/(' . $query . ')/i', '<strong>$1</strong>', escape($text));
    }
}
