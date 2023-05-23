<?php

namespace Nearata\RelatedDiscussions;

use Flarum\Discussion\Filter\DiscussionFilterer;
use Flarum\Extend;
use Nearata\RelatedDiscussions\Discussion\Filter\RelatedDiscussionsFilter;
use Nearata\RelatedDiscussions\Listener\SettingsSavingListener;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Filter(DiscussionFilterer::class))
        ->addFilter(RelatedDiscussionsFilter::class),

    (new Extend\Settings)
        ->default('nearata-related-discussions.allow-guests', false)
        ->default('nearata-related-discussions.generator', 'random')
        ->default('nearata-related-discussions.max-discussions', 5)
        ->default('nearata-related-discussions.position', 'first_post')
        ->default('nearata-related-discussions.cache', '0d0h0m')
        ->serializeToForum('nearataRelatedDiscussionsAllowGuests', 'nearata-related-discussions.allow-guests', 'boolval')
        ->serializeToForum('nearataRelatedDiscussionsPosition', 'nearata-related-discussions.position'),

    (new Extend\Event)
        ->listen(\Flarum\Settings\Event\Saving::class, SettingsSavingListener::class),
];
