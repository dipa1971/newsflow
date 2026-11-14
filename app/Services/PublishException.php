<?php

namespace App\Services;

// পোস্টিংয়ের সময় ত্রুটি হলে এটা ছোঁড়া হয়।
// retryable = আবার চেষ্টা করলে ঠিক হতে পারে (নেট সমস্যা, চাপ)।
// auth      = টোকেন বা অনুমতির সমস্যা (আবার চেষ্টায় লাভ নেই, গ্রাহককে আবার যুক্ত হতে হবে)।
class PublishException extends \RuntimeException
{
    public function __construct(string $message, public bool $retryable = false, public bool $auth = false)
    {
        parent::__construct($message);
    }
}
