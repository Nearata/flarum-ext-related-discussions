import RelatedDiscussionList from "./components/RelatedDiscussionList";
import { extend } from "flarum/common/extend";
import app from "flarum/forum/app";
import DiscussionPage from "flarum/forum/components/DiscussionPage";
import PostStream from "flarum/forum/components/PostStream";

app.initializers.add("nearata-related-discussions", () => {
  extend(PostStream.prototype, "view", function (element) {
    const allowGuests = app.forum.attribute(
      "nearataRelatedDiscussionsAllowGuests"
    );

    if (!app.session.user && !allowGuests) {
      return;
    }

    const discussionId = this.discussion.id();
    const position: string = app.forum.attribute(
      "nearataRelatedDiscussionsPosition"
    );
    const key = "nearataRelatedDiscussions";

    if (position === "first_post") {
      element.children?.splice(
        1,
        0,
        <RelatedDiscussionList
          key={key}
          discussionId={discussionId}
          position={1}
        />
      );
    }

    if (position === "last_post") {
      element.children?.splice(
        element.children.length - 1,
        0,
        <RelatedDiscussionList
          key={key}
          discussionId={discussionId}
          position={2}
        />
      );
    }
  });

  extend(DiscussionPage.prototype, "mainContent", function (items) {
    const allowGuests = app.forum.attribute(
      "nearataRelatedDiscussionsAllowGuests"
    );

    if (!app.session.user && !allowGuests) {
      return;
    }

    const discussionId = this.discussion?.id();
    const position: string = app.forum.attribute(
      "nearataRelatedDiscussionsPosition"
    );

    if (position === "reply_block") {
      items.add(
        "nearataRelatedDiscussions",
        <RelatedDiscussionList discussionId={discussionId} position={3} />
      );
    }
  });
});
