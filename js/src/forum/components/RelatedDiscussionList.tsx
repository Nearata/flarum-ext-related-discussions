import RelatedDiscussionState from "../states/RelatedDiscussionState";
import Component from "flarum/common/Component";
import LoadingIndicator from "flarum/common/components/LoadingIndicator";
import Placeholder from "flarum/common/components/Placeholder";
import app from "flarum/forum/app";
import DiscussionListItem from "flarum/forum/components/DiscussionListItem";
import type Mithril from "mithril";

export default class RelatedDiscussionList extends Component {
  relatedDiscussionState!: RelatedDiscussionState;
  discussionId!: number;
  position!: number;

  oninit(vnode: Mithril.Vnode<this>) {
    super.oninit(vnode);

    this.position = vnode.attrs.position;

    this.relatedDiscussionState = new RelatedDiscussionState(
      vnode.attrs.discussionId
    );
    this.relatedDiscussionState.load();
  }

  content() {
    if (this.relatedDiscussionState.isLoading()) {
      return <LoadingIndicator />;
    }

    if (!this.relatedDiscussionState.getData().length) {
      return (
        <Placeholder
          text={app.translator.trans(
            "nearata-related-discussions.forum.no_results"
          )}
        />
      );
    }

    return this.relatedDiscussionState.getData().map((discussion, index) => {
      return (
        <li
          data-id={discussion.id()}
          role="article"
          aria-setsize="-1"
          aria-posinset={index}
        >
          <DiscussionListItem discussion={discussion} params={{}} />
        </li>
      );
    });
  }

  view() {
    return (
      <div
        class={`DiscussionList nearataRelatedDiscussions position${this.position}`}
      >
        <h3 class="h3 DiscussionList-title">
          {app.translator.trans(
            "nearata-related-discussions.forum.discussion_list_title"
          )}
        </h3>
        <ul class="DiscussionList-discussions" role="feed">
          {this.content()}
        </ul>
      </div>
    );
  }
}
