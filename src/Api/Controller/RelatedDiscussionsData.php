<?php

namespace Nearata\RelatedDiscussions\Api\Controller;

use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\Discussion;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class RelatedDiscussionsData
{
    public function __invoke(ShowDiscussionController $controller, Discussion $discussion, ServerRequestInterface $request, Document $document)
    {
        $discussion['nearataRelatedDiscussions'] = Discussion::all()
            ->filter(function (Discussion $i) use ($discussion) {
                return $i->id != $discussion->id;
            });
    }
}
