<?php

namespace Nearata\RelatedDiscussions\Api\Controller;

use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\Discussion;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class RelatedDiscussionsData
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(ShowDiscussionController $controller, Discussion $discussion, ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $allowGuests = $this->settings->get('nearata-related-discussions.allow-guests');

        if (!$allowGuests && $actor->isGuest()) {
            return;
        }

        $discussion['nearataRelatedDiscussions'] = Discussion::all()
            ->filter(function (Discussion $i) use ($discussion) {
                return $i->id != $discussion->id;
            });
    }
}
