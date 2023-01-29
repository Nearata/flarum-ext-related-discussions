import Discussion from "flarum/common/models/Discussion";
import app from "flarum/forum/app";

export default class RelatedDiscussionState {
  discussionId: number;
  loading: boolean;
  data: Array<Discussion>;

  constructor(discussionId: number) {
    this.discussionId = discussionId;
    this.loading = true;
    this.data = [];
  }

  load(): void {
    app.store
      .find("discussions", {
        "filter[nearataRelatedDiscussions]": this.discussionId,
      })
      .then((r: any) => {
        this.data.push(...r);

        this.loading = false;

        m.redraw();
      });
  }

  isLoading(): boolean {
    return this.loading;
  }

  getData(): Array<Discussion> {
    return this.data;
  }
}
