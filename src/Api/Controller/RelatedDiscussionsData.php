<?php

namespace Nearata\RelatedDiscussions\Api\Controller;

use Carbon\Carbon;
use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\Discussion;
use Flarum\Extension\ExtensionManager;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Cache\Repository;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class RelatedDiscussionsData
{
    protected $pattern = '/^(?<days>([0-9]|[1-2][0-9]|[3][0-1]))d(?<hours>([0-9]|[1][0-9]|[2][0-3]))h(?<minutes>([0-9]|[1-5][0-9]))m$/';

    protected $settings;
    protected $extensions;
    protected $cache;

    public function __construct(SettingsRepositoryInterface $settings, ExtensionManager $extensions, Repository $cache)
    {
        $this->settings = $settings;
        $this->extensions = $extensions;
        $this->cache = $cache;
    }

    public function __invoke(ShowDiscussionController $controller, Discussion $discussion, ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $allowGuests = $this->settings->get('nearata-related-discussions.allow-guests');

        if (!$allowGuests && $actor->isGuest()) {
            return;
        }

        $cache = (string) $this->settings->get('nearata-related-discussions.cache');

        preg_match($this->pattern, $cache, $matches);
        $days = intval($matches['days']);
        $hours = intval($matches['hours']);
        $minutes = intval($matches['minutes']);

        $hasCache = $days || $hours || $minutes;

        if ($hasCache) {
            $ttl = Carbon::now()->addDays($days)->addHours($hours)->addMinutes($minutes);
        } else {
            $ttl = 0;
        }

        $results = $this->cache->remember('nearataRelatedDiscussions' . $discussion->id, $ttl, function () use ($discussion) {
            return $this->getResults($discussion);
        });

        $discussion['nearataRelatedDiscussions'] = $results;
    }

    private function getResults(Discussion $discussion)
    {
        $results = Discussion::all()
            ->filter(function (Discussion $i) use ($discussion) {
                return $i->id != $discussion->id;
            })
            ->filter(function (Discussion $i) {
                return is_null($i->hidden_at);
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

        $maxDiscussions = (int) $this->settings->get('nearata-related-discussions.max-discussions');

        if ($maxDiscussions == 0) {
            $maxDiscussions = 5;
        }

        $min = min($maxDiscussions, count($results));

        if ($generator == 'random') {
            $results = $results->random($min);
        } else {
            $results = $results->splice(0, $min);
        }

        return $results;
    }
}
