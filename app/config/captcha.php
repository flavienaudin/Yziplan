<?php if (!class_exists('CaptchaConfiguration')) { return; }
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 24/02/2017
 * Time: 10:12
 */

// BotDetect PHP Captcha configuration options
return [
    // Captcha configuration for example page
    'SuggestionCaptcha' => [
        'UserInputID' => 'captchaCode',
        'ImageWidth' => 250,
        'ImageHeight' => 50,
    ],

    // Captcha configuration for login page : As example
//    'LoginCaptcha' => [
//        'UserInputID' => 'captchaCode',
//        'CodeLength' => CaptchaRandomization::GetRandomCodeLength(4, 6),
//        'ImageStyle' => [
//            ImageStyle::Radar,
//            ImageStyle::Collage,
//            ImageStyle::Fingerprints,
//        ],
//    ],

];