<?php

namespace Celysium\Permission\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Closure;


class CheckRoutePermission
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if(!$user) {
            return $next($request);
        }

        $permissions = $user->cachePermissions();

        foreach ($permissions as $permission) {
            $requestUrl = explode('/', trim($request->getRequestUri(), '/'));

            unset($requestUrl[3]);

            $requestUrl = implode('/', array_values($requestUrl));

            $patterns = $this->getPatterns($permission['route']['url']);

            $match = false;

            foreach ($patterns as $pattern) {
                $match = preg_match($pattern, $requestUrl);
                if ($match) {
                    break;
                }
            }

            if ($match) {
                return $next($request);
            }
        }

        throw new AuthorizationException();
    }

    /**
     * @param $url
     * @return array
     */
    public function getPatterns($url): array
    {
        $data = [];

        $optionalParameters = preg_match_all('/\{(\w+?)\}\?/', $url, $matches);
        if ($optionalParameters == 0) {
            $pattern = $this->standard($url);
            $data[] = preg_replace('/\{(\w+?)\}/', '(\w+)', $pattern);
        } else {
            $subject = preg_replace('/\{(\w+)\}[S|\/|$]/', '(\w+)/', $url);

            $states = $this->subsets($matches[0], '(\w+)');

            foreach ($states as $state) {
                $pattern = str_replace(array_keys($state), array_values($state), $subject);

                $data[] = $this->standard($pattern);
            }
        }

        return $data;
    }

    /**
     * @param $url
     * @return string
     */
    private function standard($url): string
    {
        $pattern = str_replace('/', '\/', $url);
        $pattern = rtrim($pattern, '\/');
        $pattern = '/^' . $pattern . '$/';
        return str_replace('\/\/', '\/', $pattern);
    }

    /**
     * @param $items
     * @param string $exist
     * @param string $empty
     * @return array
     */
    private function subsets($items, string $exist = '', string $empty = ''): array
    {
        $result = [];
        $count = count($items);
        for ($state = 0; $state < pow(2, $count); $state++) {

            $possible = [];
            $binary = sprintf("%0$count" . "b", $state);

            for ($index = 0; $index < $count; $index++) {
                if ($binary[$index] == '1') {
                    $possible[$items[$index]] = $exist;
                } else {
                    $possible[$items[$index]] = $empty;
                }
            }
            $result[] = $possible;
        }

        return $result;
    }
}