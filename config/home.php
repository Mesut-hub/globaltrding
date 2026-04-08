<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Home Hero Public Video Override
    |--------------------------------------------------------------------------
    |
    | When set, the home hero block will serve this public path instead of the
    | CMS-stored media. Place your file at public/media/hero.mp4 and set the
    | environment variable:
    |
    |   HOME_HERO_PUBLIC_VIDEO=/media/hero.mp4
    |
    | Leave empty (or unset) to use the CMS-selected media as normal.
    |
    */
    'hero_public_video' => env('HOME_HERO_PUBLIC_VIDEO', ''),
];
