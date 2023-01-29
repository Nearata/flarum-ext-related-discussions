import RelatedDiscussionList from "./components/RelatedDiscussionList";
import { extend } from "flarum/common/extend";
import app from "flarum/forum/app";
import PostStream from "flarum/forum/components/PostStream";

app.initializers.add("nearata/related-discussions", () => {
  extend(PostStream.prototype, "view", function (element) {
    const discussionId = this.discussion.id();
    const position: string = app.forum.attribute(
      "nearataRelatedDiscussionsPosition"
    );

    if (position === "first_post") {
      element.children.splice(
        1,
        0,
        m(RelatedDiscussionList, { discussionId, position: 1 })
      );
    }

    if (position === "last_post") {
      element.children.splice(
        element.children.length - 1,
        0,
        m(RelatedDiscussionList, { discussionId, position: 2 })
      );
    }
  });
});
