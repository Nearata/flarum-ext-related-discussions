import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Placeholder from 'flarum/common/components/Placeholder';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';

import type Mithril from 'mithril';
import Discussion from 'flarum/common/models/Discussion';

export default class RelatedDiscussionList extends Component {
  discussions: Array<Discussion> = [];
  position: number = 1;

  oninit(vnode: Mithril.Vnode<this>) {
    super.oninit(vnode);

    this.discussions = this.attrs.discussions;
    this.position = this.attrs.position;
  }

  view() {
    const allowGuests = app.forum.attribute('nearataRelatedDiscussionsAllowGuests');

    if (!app.session.user && !allowGuests) {
      return;
    }

    return m(`.DiscussionList.nearataRelatedDiscussions.position${this.position}`, [
      m('h3.DiscussionList-title', app.translator.trans('nearata-related-discussions.forum.discussion_list_title')),
      this.discussions.length
        ? [
            m('ul.DiscussionList-discussions[role=feed]', [
              this.discussions.map((discussion, index) => {
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
    ]);
  }
}
