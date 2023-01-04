import app from 'flarum/forum/app';
import { override } from 'flarum/common/extend';
import PostStream from 'flarum/forum/components/PostStream';
import Discussion from 'flarum/common/models/Discussion';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import Placeholder from 'flarum/common/components/Placeholder';

app.initializers.add('nearata/related-discussions', () => {
  app.store.models.nearataRelatedDiscussions = Discussion;
  Discussion.prototype.nearataRelatedDiscussions = Discussion.hasMany<Discussion>('nearataRelatedDiscussions');

  override(PostStream.prototype, 'view', function (original) {
    const allowGuests = app.forum.attribute('nearataRelatedDiscussionsAllowGuests');

    if (!app.session.user && !allowGuests) {
      return original();
    }

    const discussions: Array<Discussion> = this.discussion.nearataRelatedDiscussions();

    return [
      original(),
      m('.DiscussionList.nearataRelatedDiscussions', [
        m('h3.DiscussionList-title', app.translator.trans('nearata-related-discussions.forum.discussion_list_title')),
        discussions
          ? [
              m('ul.DiscussionList-discussions[role=feed]', [
                discussions.map((discussion, index) => {
                  return m(
                    'li',
                    {
                      'data-id': discussion.id(),
                      role: 'article',
                      'aria-setsize': '-1',
                      'aria-posinset': index,
                    },
                    [m(DiscussionListItem, { discussion: discussion, params: {} })]
                  );
                }),
              ]),
            ]
          : m(Placeholder, { text: app.translator.trans('nearata-related-discussions.forum.no_results') }),
      ]),
    ];
  });
});
