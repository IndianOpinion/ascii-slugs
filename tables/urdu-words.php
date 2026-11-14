<?php

/**
 * Exception list for common Urdu words.
 *
 * Urdu leaves most short vowels unwritten, so the letter rules give a
 * consonant skeleton. These common words read much better spelled out.
 * Keys are the word as written, without short vowel marks. Keep the list
 * short: at most 20 entries in 0.1.0 (see docs/ARCHITECTURE.md, D4).
 */

declare(strict_types=1);

return [
    'اردو' => 'urdu',
    'اور' => 'aur',
    'میں' => 'mein',
    'ہے' => 'hai',
    'ہیں' => 'hain',
    'یہ' => 'yeh',
    'وہ' => 'woh',
    'پر' => 'par',
    'نہیں' => 'nahin',
    'کیا' => 'kya',
    'بھارت' => 'bharat',
    'ہندوستان' => 'hindustan',
    'دہلی' => 'delhi',
    'حکومت' => 'hukumat',
    'وزیر' => 'wazir',
    'سرکار' => 'sarkar',
    'خبر' => 'khabar',
    'تک' => 'tak',
    'ایک' => 'ek',
    'لیکن' => 'lekin',
];
