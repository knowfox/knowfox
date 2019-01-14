<?php

return [
    'title' => 'Login',
    'above' => 
          'On :app, all content is private by default.'
        . 'To verify your identity, we need to send you an email with a link.'
        . 'Click this link and you get access to your account.',
    'prompt' => 'Please enter your email address to log into :app.',
    'below' => [
        [
            'title' => 'Why do we have to send you an email?',
            'text' => 
                  'We use email to log you into :app.'
                . 'This way, you don\'t have to remember a password.'
                . 'You stay logged in until you explicitly log out.',
        ],
        [
            'title' => 'Is this secure?',
            'text' => 
                  'Totally! To log into :app, you have to click on that link in the email we send you.'
                . 'This way, nowbody has access to your :app account, '
                . 'without also having access to your email account.',
        ]
    ],
];