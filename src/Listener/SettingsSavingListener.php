<?php

namespace Nearata\RelatedDiscussions\Listener;

use Flarum\Settings\Event\Saving;
use Illuminate\Support\Arr;
use Nearata\RelatedDiscussions\Validator\SettingsSavingValidator;

class SettingsSavingListener
{
    protected $validator;

    public function __construct(SettingsSavingValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Saving $event)
    {
        $setting = Arr::get($event->settings, 'nearata-related-discussions.cache');

        if (is_null($setting)) {
            return;
        }

        $this->validator->assertValid(['cache' => $setting]);
    }
}
