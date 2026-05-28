<?php

namespace App\Services;

class LemmatizationService
{
    /**
     * Ukrainian suffix → replacement map.
     * Sorted by suffix length descending so longer suffixes match first.
     */
    private array $suffixMap = [
        // 4-char suffixes
        'ками' => 'ки',
        'ями' => 'я',
        'ими' => 'ий',
        'ами' => 'и',
        // 3-char suffixes
        'ого' => 'ий',
        'ому' => 'ий',
        'ові' => '',
        'еві' => '',
        'ної' => 'ний',
        'них' => 'ний',
        'ним' => 'ний',
        'ною' => 'ний',
        'іші' => 'ий',
        'іше' => 'ий',
        'ків' => 'к',
        'оть' => 'ати',
        'ють' => 'ати',
        'ять' => 'ати',
        'ить' => 'ити',
        'ись' => 'ися',
        // 2-char suffixes
        'ів' => '',
        'їв' => 'й',
        'ей' => 'ь',
        'ок' => 'ка',
        'ки' => 'ка',
        'ці' => 'ка',
        'ою' => 'а',
        'ої' => 'а',
        'ій' => 'а',
        'ім' => 'ий',
        'іх' => 'ий',
        'их' => 'ий',
        'им' => 'ий',
        'ах' => '',
        'ях' => '',
        'ом' => '',
        'ям' => 'я',
        'ам' => '',
        'ні' => 'ний',
        'ну' => '',
        'ку' => 'к',
        'ти' => '',
        'ує' => 'увати',
        'ає' => 'ати',
        'іє' => 'іти',
    ];

    /**
     * Minimum word length to attempt lemmatization.
     * Short words are returned as-is to avoid false stems.
     */
    private int $minWordLength = 4;

    /**
     * Lemmatize text: returns original text + generated stems joined by space.
     * This enriches the indexed content so Meilisearch can match morphological variants.
     */
    public function lemmatize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        if ($text === '') {
            return '';
        }

        $words = preg_split('/[\s\-_.,;:!?()]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return $text;
        }

        $stems = [];

        foreach ($words as $word) {
            $stem = $this->stemWord($word);

            if ($stem !== null && $stem !== $word) {
                $stems[] = $stem;
            }
        }

        if (empty($stems)) {
            return $text;
        }

        return $text . ' ' . implode(' ', array_unique($stems));
    }

    /**
     * Expand a search query with morphological variants.
     * Unlike lemmatize(), this returns stems alongside originals for query broadening.
     */
    public function expandQuery(string $query): string
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') {
            return '';
        }

        $words = preg_split('/[\s\-_.,;:!?()]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return $query;
        }

        $expanded = [];

        foreach ($words as $word) {
            $expanded[] = $word;

            $stem = $this->stemWord($word);
            if ($stem !== null && $stem !== $word) {
                $expanded[] = $stem;
            }
        }

        return implode(' ', array_unique($expanded));
    }

    /**
     * Attempt to stem a single Ukrainian word by stripping known suffixes.
     * Returns the stem or null if no suffix matched.
     */
    private function stemWord(string $word): ?string
    {
        $length = mb_strlen($word);

        if ($length < $this->minWordLength) {
            return null;
        }

        // Only process Cyrillic words
        if (!preg_match('/^[\p{Cyrillic}]+$/u', $word)) {
            return null;
        }

        foreach ($this->suffixMap as $suffix => $replacement) {
            $suffixLen = mb_strlen($suffix);

            if ($suffixLen >= $length) {
                continue;
            }

            if (mb_substr($word, -$suffixLen) === $suffix) {
                $stem = mb_substr($word, 0, $length - $suffixLen) . $replacement;

                // Ensure stem is at least 2 characters
                if (mb_strlen($stem) >= 2) {
                    return $stem;
                }
            }
        }

        return null;
    }
}
