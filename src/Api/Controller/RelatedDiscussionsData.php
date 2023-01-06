<?php

namespace Nearata\RelatedDiscussions\Api\Controller;

use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\Discussion;
use Flarum\Extension\ExtensionManager;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class RelatedDiscussionsData
{
    protected $settings;
    protected $extensions;

    public function __construct(SettingsRepositoryInterface $settings, ExtensionManager $extensions)
    {
        $this->settings = $settings;
        $this->extensions = $extensions;
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
                if (!$this->extensions->isEnabled('flarum-tags')) {
                    return true;
                }

                $tags = $discussion->tags->map(function ($i) {
                    return $i->name;
                })->first();

                return $i->tags->firstWhere('name', $tags);
            })
            // flarum/approval
            ->filter(function (Discussion $i) {
                if (!$this->extensions->isEnabled('flarum-approval')) {
                    return true;
                }

                return $i->is_approved;
            });

        $generator = $this->settings->get('nearata-related-discussions.generator');

        if ($generator == 'title') {
            $results = $results->filter(function (Discussion $i) use ($discussion) {
                $perc = 0;
                similar_text(strtolower($discussion->title), strtolower($i->title), $perc);
                return $perc > 60;
            });
        }

        $min = min($maxDiscussions, count($results));

        if ($generator == 'random') {
            $results = $results->random($min);
        } else {
            $results = $results->splice(0, $min);
        }

        $discussion['nearataRelatedDiscussions'] = $results;
    }
}
