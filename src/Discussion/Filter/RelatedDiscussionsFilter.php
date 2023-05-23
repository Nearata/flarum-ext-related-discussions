<?php

namespace Nearata\RelatedDiscussions\Discussion\Filter;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Extension\ExtensionManager;
use Flarum\Filter\FilterInterface;
use Flarum\Filter\FilterState;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Cache\Repository;

class RelatedDiscussionsFilter implements FilterInterface
{
    protected $pattern = '/^(?<days>([0-9]|[1-2][0-9]|[3][0-1]))d(?<hours>([0-9]|[1][0-9]|[2][0-3]))h(?<minutes>([0-9]|[1-5][0-9]))m$/';

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var ExtensionManager
     */
    protected $extensions;

    /**
     * @var Repository
     */
    protected $cache;

    public function __construct(SettingsRepositoryInterface $settings, ExtensionManager $extensions, Repository $cache)
    {
        $this->settings = $settings;
        $this->extensions = $extensions;
        $this->cache = $cache;
    }

    public function getFilterKey(): string
    {
        return 'nearataRelatedDiscussions';
    }

    public function filter(FilterState $filterState, string $filterValue, bool $negate)
    {
        if (! $filterValue) {
            return;
        }

        $discussionId = intval($filterValue);

        if ($discussionId == 0) {
            return;
        }

        /** @var ?Discussion */
        $discussion = Discussion::find($discussionId);

        if (is_null($discussion)) {
            return;
        }

        $allowGuests = $this->settings->get('nearata-related-discussions.allow-guests');

        if (! $allowGuests && $filterState->getActor()->isGuest()) {
            return;
        }

        $cache = (string) $this->settings->get('nearata-related-discussions.cache');

        preg_match($this->pattern, $cache, $matches);

        $days = intval($matches['days']);
        $hours = intval($matches['hours']);
        $minutes = intval($matches['minutes']);

        if ($days || $hours || $minutes) {
            $ttl = Carbon::now()->addDays($days)->addHours($hours)->addMinutes($minutes);
        } else {
            $ttl = 0;
        }

        $ids = $this->cache->remember('nearataRelatedDiscussions'.$discussionId, $ttl, function () use ($discussion) {
            return $this->getResults($discussion);
        });

        $filterState->getQuery()->whereIn('id', $ids);
    }

    private function getResults(Discussion $discussion)
    {
        /** @var \Illuminate\Database\Query\Builder */
        $query = Discussion::query()
            ->whereKeyNot($discussion->id)
            ->whereNull('hidden_at');

        if ($this->extensions->isEnabled('flarum-approval')) {
            $query = $query->where('is_approved', '=', 1);
        }

        if ($this->extensions->isEnabled('flarum-tags')) {
            /** @var int */
            $tagId = $discussion->tags->map(function ($i) {
                return $i->id;
            })->first();

            $query = $query->with('tags')->whereHas('tags', function ($query) use ($tagId) {
                $query->where('tag_id', '=', $tagId);
            });
        }

        $maxDiscussions = (int) $this->settings->get('nearata-related-discussions.max-discussions');

        if ($maxDiscussions <= 0) {
            $maxDiscussions = 5;
        }

        $generator = (string) $this->settings->get('nearata-related-discussions.generator');

        if ($generator == 'title') {
            $results = collect([], $maxDiscussions);

            $query->select('id', 'title')->chunk(200, function ($collection) use ($results, $discussion, $maxDiscussions) {
                $mainDiscussionTitle = strtolower($discussion->title);

                foreach ($collection as $i) {
                    if ($results->count() == $maxDiscussions) {
                        return false;
                    }

                    $perc = 0;
                    similar_text($mainDiscussionTitle, strtolower($i->title), $perc);

                    if ($perc > 60) {
                        $results->push($i->id);
                    }
                }
            });
        }

        if ($generator == 'random') {
            $results = $query->inRandomOrder()
                ->limit($maxDiscussions)
                ->get('id')
                ->map(function (Discussion $discussion) {
                    return $discussion->id;
                });
        }

        return $results;
    }
}
