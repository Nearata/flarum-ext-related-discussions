<?php

namespace Nearata\RelatedDiscussions;

use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Nearata\RelatedDiscussions\Api\Controller\RelatedDiscussionsData;
use Nearata\RelatedDiscussions\Api\Serializer\RelatedDiscussionsSerializer;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(Discussion::class))
        ->belongsToMany('nearataRelatedDiscussions', Discussion::class, 'nearata_related_discussions', 'discussion_id', 'related_discussion_id'),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->hasMany('nearataRelatedDiscussions', RelatedDiscussionsSerializer::class),

    (new Extend\ApiController(ShowDiscussionController::class))
        ->addInclude(['nearataRelatedDiscussions', 'nearataRelatedDiscussions.user', 'nearataRelatedDiscussions.tags'])
        ->prepareDataForSerialization(RelatedDiscussionsData::class),

    (new Extend\Settings)
        ->default('nearata-related-discussions.allow-guests', false)
        ->default('nearata-related-discussions.generator', 'random')
        ->default('nearata-related-discussions.algorithm', 'similar_text')
        ->default('nearata-related-discussions.max-discussions', 5)
        ->serializeToForum('nearataRelatedDiscussionsAllowGuests', 'nearata-related-discussions.allow-guests', function ($value) {
            return boolval($value);
        })
];
