<?php

namespace Core;

class EcosystemIntegration
{
    public static function registry(): array
    {
        return Helpers::config('ecosystem_integrations', []);
    }

    public static function route(string $routeKey, array $params = [], array $query = []): ?string
    {
        $registry = self::registry();
        $route = $registry['routes'][$routeKey] ?? null;
        if (!$route || empty($route['type'])) {
            return null;
        }

        $path = $route['path'] ?? '';
        if ($path === '') {
            return null;
        }

        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', rawurlencode((string) $value), $path);
        }

        if (preg_match('/\{[a-zA-Z0-9_]+\}/', $path)) {
            return null;
        }

        if ($route['type'] === 'project') {
            $app = (string) ($route['app'] ?? '');
            if ($app === '' || !self::isAppAvailable($app)) {
                return null;
            }
            $path = self::projectBase($app) . '/' . ltrim($path, '/');
        }

        if (!empty($query)) {
            $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query($query);
        }

        return $path;
    }

    public static function actionsForEntity(string $entityType, array $context = []): array
    {
        $registry = self::registry();
        $entity = $registry['entities'][$entityType] ?? null;
        if (!$entity || empty($entity['actions']) || !is_array($entity['actions'])) {
            return [];
        }

        $actions = [];
        foreach ($entity['actions'] as $action) {
            if (!self::isActionEligible($action, $context)) {
                continue;
            }

            if (($action['type'] ?? 'link') === 'qr_modal') {
                $urlKey = (string)($action['url_from'] ?? '');
                $qrUrl = (string)($context[$urlKey] ?? '');
                if ($qrUrl === '') {
                    continue;
                }

                $actions[] = [
                    'type'  => 'qr_modal',
                    'url'   => $qrUrl,
                    'label' => (string)($action['label'] ?? 'QR'),
                    'icon'  => (string)($action['icon'] ?? 'fa-qrcode'),
                    'title' => (string)($action['title'] ?? 'Generate QR'),
                    'class' => (string)($action['class'] ?? 'btn btn-secondary btn-sm'),
                    'style' => (string)($action['style'] ?? ''),
                ];
                continue;
            }

            $params = [];
            foreach (($action['params'] ?? []) as $paramKey => $contextKey) {
                $params[$paramKey] = $context[$contextKey] ?? null;
            }

            $query = [];
            foreach (($action['query'] ?? []) as $queryKey => $contextKey) {
                $query[$queryKey] = $context[$contextKey] ?? null;
            }
            $query = array_filter($query, static fn($v) => $v !== null && $v !== '');

            $url = self::route((string)$action['route'], $params, $query);
            if (!$url && !empty($action['fallback_route'])) {
                $url = self::route((string)$action['fallback_route'], $params, $query);
            }
            if (!$url) {
                continue;
            }

            $actions[] = [
                'type'   => 'link',
                'url'    => $url,
                'label'  => (string)($action['label'] ?? 'Open'),
                'icon'   => (string)($action['icon'] ?? 'fa-external-link-alt'),
                'title'  => (string)($action['title'] ?? 'Open'),
                'class'  => (string)($action['class'] ?? 'btn btn-secondary btn-sm'),
                'style'  => (string)($action['style'] ?? ''),
                'target' => !empty($action['target_blank']) ? '_blank' : null,
            ];
        }

        return $actions;
    }

    public static function isAppAvailable(string $app): bool
    {
        return Helpers::isProjectEnabled($app);
    }

    public static function projectBase(string $app): string
    {
        $registry = self::registry();
        $preferShort = (bool)($registry['apps'][$app]['prefer_short'] ?? false);
        if ($preferShort) {
            return '/' . $app;
        }
        return '/projects/' . $app;
    }

    public static function logHandoff(?int $userId, string $fromApp, string $toApp, string $action, array $data = []): void
    {
        ActivityLogger::log($userId, 'ecosystem_handoff', [
            'module'        => $fromApp,
            'resource_type' => 'integration',
            'entity_name'   => $action,
            'status'        => 'success',
            'data'          => array_merge($data, [
                'from_app' => $fromApp,
                'to_app'   => $toApp,
                'action'   => $action,
            ]),
        ]);
    }

    private static function isActionEligible(array $action, array $context): bool
    {
        foreach (($action['requires'] ?? []) as $key) {
            if (!isset($context[$key]) || $context[$key] === '') {
                return false;
            }
        }

        if (($action['validate'] ?? null) === 'url') {
            $keys = $action['requires'] ?? [];
            foreach ($keys as $key) {
                if (isset($context[$key]) && $context[$key] !== '' && !filter_var($context[$key], FILTER_VALIDATE_URL)) {
                    return false;
                }
            }
        }

        $routeKey = (string)($action['route'] ?? '');
        if ($routeKey !== '') {
            $registry = self::registry();
            $route = $registry['routes'][$routeKey] ?? null;
            if (($route['type'] ?? '') === 'project') {
                $app = (string)($route['app'] ?? '');
                if ($app !== '' && !self::isAppAvailable($app)) {
                    return false;
                }
            }
        }

        return true;
    }
}
