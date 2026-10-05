<?php

namespace Tests\Contract;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records every /api request a feature test makes into
 * api/contract/fixtures/<METHOD>-<route-slug>/<case>.json when the
 * CONTRACT_RECORD environment variable is set. Each fixture holds the
 * database state right before the request (so it can be replayed on its
 * own), who was signed in, the request and the normalised response.
 *
 * See api/contract/README.md.
 */
trait RecordsContract
{
    private static ?Normalizer $contractNormalizer = null;
    private array $contractCounters = [];
    private array $contractConfigBaseline = [];

    /** Config the framework itself changes while handling requests or actingAs(). */
    private const CONTRACT_CONFIG_IGNORE = ['app.locale', 'auth.defaults.guard'];

    /** Called by Laravel's setUpTraits(): remember the untouched config. */
    protected function setUpRecordsContract(): void
    {
        if (env('CONTRACT_RECORD')) {
            $this->contractConfigBaseline = $this->contractFlatConfig();
        }
    }

    private function contractFlatConfig(): array
    {
        $out = [];
        $walk = function (array $data, string $prefix) use (&$walk, &$out) {
            foreach ($data as $k => $v) {
                $key = $prefix . $k;
                if (is_array($v) && $v !== [] && ! array_is_list($v)) {
                    $walk($v, $key . '.');
                } elseif (is_scalar($v) || $v === null || is_array($v)) {
                    $out[$key] = $v;
                }
            }
        };
        $walk($this->app['config']->all(), '');
        return $out;
    }

    /** Config keys the test changed from the defaults (e.g. a feature flag). */
    private function contractConfigOverrides(): array
    {
        $out = [];
        foreach ($this->contractFlatConfig() as $k => $v) {
            if (in_array($k, self::CONTRACT_CONFIG_IGNORE, true)) {
                continue;
            }
            if (! array_key_exists($k, $this->contractConfigBaseline) || $this->contractConfigBaseline[$k] !== $v) {
                $out[$k] = $v;
            }
        }
        ksort($out);
        return $out;
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: $uri;
        if (! env('CONTRACT_RECORD') || ! Str::startsWith(ltrim($path, '/'), 'api/')) {
            return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        }

        $before = [
            'recorded_at' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'seed' => $this->contractSnapshot(),
            'auth' => $this->contractAuth(),
            'config' => $this->contractConfigOverrides(),
            // Read uploads now: the request may move the temporary files.
            'files' => array_merge($this->contractFiles($files), $this->contractFiles($this->contractOnlyFiles($parameters))),
        ];

        $response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);

        try {
            $this->contractWrite($method, $uri, $parameters, $files, $server, $content, $before, $response);
        } catch (\Throwable $e) {
            fwrite(STDERR, "\n[contract] could not record {$method} {$uri}: {$e->getMessage()}\n");
        }

        return $response;
    }

    private function contractAuth(): ?array
    {
        $auth = $this->app['auth'];
        foreach (['sanctum', 'web'] as $guard) {
            try {
                $g = $auth->guard($guard);
            } catch (\Throwable $e) {
                continue;
            }
            if (method_exists($g, 'hasUser') && $g->hasUser()) {
                return ['type' => 'bearer', 'user_id' => $g->user()->getAuthIdentifier()];
            }
        }
        return null;
    }

    private function contractSnapshot(): array
    {
        $db = DB::connection();
        $driver = $db->getDriverName();
        $tables = [];
        $autoIncrement = [];

        if ($driver === 'mysql') {
            $names = array_map(fn ($r) => array_values((array) $r)[0], $db->select('SHOW TABLES'));
            foreach ($db->select('SELECT TABLE_NAME AS t, AUTO_INCREMENT AS ai FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()') as $r) {
                if ($r->ai !== null) {
                    $autoIncrement[$r->t] = (int) $r->ai;
                }
            }
        } else {
            $names = array_map(fn ($r) => $r->name, $db->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"));
        }

        sort($names);
        foreach ($names as $table) {
            if ($table === 'migrations') {
                continue;
            }
            $rows = $db->table($table)->get()->map(fn ($r) => (array) $r)->all();
            if ($rows) {
                $tables[$table] = $rows;
            }
        }
        ksort($autoIncrement);

        return ['driver' => $driver, 'tables' => (object) $tables, 'auto_increment' => (object) $autoIncrement];
    }

    private function contractWrite($method, $uri, $parameters, $files, $server, $content, array $before, $response): void
    {
        $base = base_path('api/contract');
        self::$contractNormalizer ??= new Normalizer($base . '/normalize.json');

        $route = $this->app['router']->getCurrentRoute();
        $routeUri = $route ? $route->uri() : ltrim(parse_url($uri, PHP_URL_PATH), '/');
        $slug = strtoupper($method) . '-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(str_replace(['{', '}'], '', $routeUri))), '-');

        $test = static::class . '::' . $this->name();
        $caseBase = class_basename(static::class) . '__' . preg_replace('/^test_?/', '', $this->name());
        $this->contractCounters[$slug . $caseBase] = ($this->contractCounters[$slug . $caseBase] ?? 0) + 1;
        $case = $caseBase . '__' . $this->contractCounters[$slug . $caseBase];

        // Request
        $path = parse_url($uri, PHP_URL_PATH);
        parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $query);
        $headers = [];
        foreach ($server as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
                if (! in_array($name, ['Cookie', 'Host', 'Content-Length'], true)) {
                    $headers[$name] = $v;
                }
            } elseif ($k === 'CONTENT_TYPE') {
                $headers['Content-Type'] = $v;
            }
        }
        // Request::create() adds this default, and it decides the response
        // language (SetApiLocale), so the replay has to send it too.
        $headers['Accept-Language'] ??= 'en-us,en;q=0.5';
        ksort($headers);

        $request = ['method' => strtoupper($method), 'path' => $path, 'query' => (object) $query, 'headers' => (object) $headers];
        if ($content !== null && $content !== '') {
            $decoded = json_decode($content, true);
            $request['json'] = $decoded === null && $content !== 'null' ? null : $decoded;
            if ($request['json'] === null) {
                $request['raw'] = $content;
            }
        } elseif (strtoupper($method) === 'GET') {
            $request['query'] = (object) array_merge($query, $parameters);
        } elseif ($form = $this->contractWithoutFiles($parameters)) {
            $request['form'] = $form;
        }
        if ($before['files']) {
            $request['files'] = $before['files'];
        }

        // Response
        $raw = $response->baseResponse->getContent();
        $json = json_decode($raw, true);
        $resp = [
            'status' => $response->getStatusCode(),
            'content_type' => strtok((string) $response->headers->get('Content-Type'), ';'),
        ];
        if ($json !== null || $raw === 'null') {
            $resp['json'] = self::$contractNormalizer->normalize($json);
        } else {
            $resp['body_sha1'] = sha1($raw);
        }

        $fixture = [
            'name' => $case,
            'test' => $test,
            'route' => strtoupper($method) . ' ' . $routeUri,
            'replayable' => $response->getStatusCode() !== 429,
            'recorded_at' => $before['recorded_at'],
            'timezone' => $before['timezone'],
            'auth' => $before['auth'],
            'config' => (object) $before['config'],
            'request' => $request,
            'response' => $resp,
            'seed' => $before['seed'],
        ];
        if (! $fixture['replayable']) {
            $fixture['not_replayable_reason'] = 'Rate-limit state lives in the cache, not in the database.';
        }

        $dir = $base . '/fixtures/' . $slug;
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/' . $case . '.json', json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    }

    private function contractOnlyFiles(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v instanceof UploadedFile) {
                $out[$k] = $v;
            } elseif (is_array($v) && ($nested = $this->contractOnlyFiles($v))) {
                $out[$k] = $nested;
            }
        }
        return $out;
    }

    private function contractWithoutFiles(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v instanceof UploadedFile) {
                continue;
            }
            $out[$k] = is_array($v) ? $this->contractWithoutFiles($v) : $v;
        }
        return $out;
    }

    private function contractFiles(array $files, string $prefix = ''): array
    {
        $out = [];
        foreach ($files as $field => $file) {
            $name = $prefix === '' ? $field : $prefix . '[' . $field . ']';
            if (is_array($file)) {
                $out = array_merge($out, $this->contractFiles($file, $name));
            } elseif ($file instanceof UploadedFile) {
                $out[] = [
                    'field' => $name,
                    'filename' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'base64' => base64_encode(file_get_contents($file->getRealPath())),
                ];
            }
        }
        return $out;
    }
}
