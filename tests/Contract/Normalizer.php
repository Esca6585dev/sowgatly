<?php

namespace Tests\Contract;

/**
 * Replaces volatile values (timestamps, tokens, hosts, random file names)
 * with placeholders. The rules live in api/contract/normalize.json and the
 * Go replay applies the same file, so both sides agree on what is stable.
 */
class Normalizer
{
    private array $keys;
    private array $patterns;
    private string $urlRegex;
    private string $pathRegex;
    private string $randomSegment;

    public function __construct(string $rulesFile)
    {
        $rules = json_decode(file_get_contents($rulesFile), true, 512, JSON_THROW_ON_ERROR);
        $this->keys = $rules['keys'];
        $this->patterns = array_map(fn ($p) => ['re' => '~' . $p['regex'] . '~u', 'ph' => $p['placeholder']], $rules['patterns']);
        $this->urlRegex = '~' . $rules['url']['regex'] . '~u';
        $this->pathRegex = '~' . $rules['url']['path_regex'] . '~u';
        $this->randomSegment = '~' . $rules['url']['random_segment'] . '~u';
    }

    public function normalize(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && array_key_exists($key, $this->keys) && $value !== null) {
            return $this->keys[$key];
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->normalize($v, is_string($k) ? $k : null);
            }
            return $out;
        }
        if (! is_string($value)) {
            return $value;
        }
        foreach ($this->patterns as $p) {
            if (preg_match($p['re'], $value)) {
                return $p['ph'];
            }
        }
        if (preg_match($this->urlRegex, $value)) {
            $path = parse_url($value, PHP_URL_PATH) ?? '';
            $query = parse_url($value, PHP_URL_QUERY);
            return '<url>' . $this->normalizePath($path) . ($query !== null ? '?' . $query : '');
        }
        if (preg_match($this->pathRegex, $value)) {
            return $this->normalizePath($value);
        }
        return $value;
    }

    private function normalizePath(string $path): string
    {
        $segments = array_map(fn ($s) => preg_match($this->randomSegment, $s) ? '<file>' : $s, explode('/', $path));
        return implode('/', $segments);
    }
}
