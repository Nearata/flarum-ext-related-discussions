import app from 'flarum/forum/app';
import { override } from 'flarum/common/extend';
import PostStream from 'flarum/forum/components/PostStream';
import Discussion from 'flarum/common/models/Discussion';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';

app.initializers.add('nearata/related-discussions', () => {
  app.store.models.nearataRelatedDiscussions = Discussion;
  Discussion.prototype.nearataRelatedDiscussions = Discussion.hasMany<Discussion>('nearataRelatedDiscussions');

  override(PostStream.prototype, 'view', function (original) {
    const discussions: Array<Discussion> = this.discussion.nearataRelatedDiscussions();

    const allowGuests = app.forum.attribute('nearataRelatedDiscussionsAllowGuests');

    if (!app.session.user && !allowGuests) {
      return original();
    }

    return [
      original(),
      m('.DiscussionList.nearataRelatedDiscussions', [
        m('h3', app.translator.trans('nearata-related-discussions.forum.discussion_list_title')),
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
      ]),
    ];
  });
});
