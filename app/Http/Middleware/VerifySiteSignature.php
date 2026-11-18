<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySiteSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $siteKey = (string) $request->header('X-NF-Site');
        $time = (string) $request->header('X-NF-Time');
        $sign = strtolower((string) $request->header('X-NF-Sign'));

        if ($siteKey === '' || $sign === '' || !ctype_digit($time)) {
            return $this->deny('অনুরোধে সই নেই বা অসম্পূর্ণ।');
        }

        if (abs(now()->timestamp - (int) $time) > 300) {
            return $this->deny('অনুরোধের সময় ৫ মিনিটের বেশি পুরনো, অথবা সাইটের ঘড়ি ঠিক নেই।');
        }

        $site = Site::where('site_key', $siteKey)->first();

        $secret = $site ? (string) $site->secret : 'x';
        $expected = hash_hmac('sha256', $time . "\n" . $request->getContent(), $secret);

        if (!$site || !hash_equals($expected, $sign)) {
            return $this->deny('সাইট আইডি বা গোপন চাবি মিলছে না।');
        }

        $request->attributes->set('site', $site);

        return $next($request);
    }

    private function deny(string $message): Response
    {
        return response()->json(['ok' => false, 'message' => $message], 401);
    }
}
