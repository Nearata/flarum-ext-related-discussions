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

        $maxDiscussions = (int) $this->settings->get('nearata-related-discussions.max-discussions');

        if ($maxDiscussions == 0) {
            $maxDiscussions = 5;
        }

        $results = Discussion::all()
            ->filter(function (Discussion $i) use ($discussion) {
                return $i->id != $discussion->id;
            })
            // flarum/tags
            ->filter(function (Discussion $i) use ($discussion) {
                if (is_null($discussion->tags)) {
                    return true;
                }

                $tags = $discussion->tags->map(function ($i) {
                    return $i->name;
                })->first();

                return $i->tags->firstWhere('name', $tags);
            });

        $alg = $this->settings->get('nearata-related-discussions.algorithm');

        if ($alg == 'random') {
            $results = $results->shuffle();
            $results = $results->splice(0, $maxDiscussions);
        }

        $discussion['nearataRelatedDiscussions'] = $results->splice(0, $maxDiscussions);
    }
}
