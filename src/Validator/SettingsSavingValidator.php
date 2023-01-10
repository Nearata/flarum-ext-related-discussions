<?php

namespace Nearata\RelatedDiscussions\Validator;

use Flarum\Foundation\AbstractValidator;

class SettingsSavingValidator extends AbstractValidator
{
    protected $rules = [
        'cache' => [
            'required',
            'regex:/^([0-9]|[1-2][0-9]|[3][0-1])d([0-9]|[1][0-9]|[2][0-3])h([0-9]|[1-5][0-9])m$/',
        ],
    ];
}
