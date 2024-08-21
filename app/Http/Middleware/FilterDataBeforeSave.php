<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\ParameterBag;

class FilterDataBeforeSave
{
    public function __construct()
    {
    }

    public function handle($request, Closure $next)
    {
        if ($request->getMethod() == 'POST') {
            if ($request->isJson()) {
                $this->clean($request->json());
            } else {
                $this->clean($request->request);
            }
        }

        return $next($request);
    }

    private function clean(ParameterBag $bag)
    {
        $bag->replace($this->cleanData($bag->all()));
    }

    private function cleanData(array $data)
    {
        return collect($data)->map(function ($value, $key) {
            if (is_string($value))
                return trim(strip_tags($value));
            else
                return $value;
        })->all();
    }
}
