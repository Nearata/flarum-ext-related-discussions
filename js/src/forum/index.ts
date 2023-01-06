import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import PostStream from 'flarum/forum/components/PostStream';
import Discussion from 'flarum/common/models/Discussion';
import RelatedDiscussionList from './components/RelatedDiscussionList';

app.initializers.add('nearata/related-discussions', () => {
  app.store.models.nearataRelatedDiscussions = Discussion;
  Discussion.prototype.nearataRelatedDiscussions = Discussion.hasMany<Discussion>('nearataRelatedDiscussions');

  extend(PostStream.prototype, 'view', function (element) {
    const position: string = app.forum.attribute('nearataRelatedDiscussionsPosition');
    const discussions: Array<Discussion> = this.discussion.nearataRelatedDiscussions();

    if (position === 'first_post') {
      element.children.splice(1, 0, m(RelatedDiscussionList, { discussions, position: 1 }));
    }

    if (position === 'last_post') {
      element.children.splice(element.children.length - 1, 0, m(RelatedDiscussionList, { discussions, position: 2 }));
    }
  });
});
