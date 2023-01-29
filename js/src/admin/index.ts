import app from "flarum/admin/app";

app.initializers.add("nearata/related-discussions", () => {
  app.extensionData
    .for("nearata-related-discussions")
    .registerSetting({
      setting: "nearata-related-discussions.allow-guests",
      type: "boolean",
      label: app.translator.trans(
        "nearata-related-discussions.admin.settings.allow_guests"
      ),
    })
    .registerSetting({
      setting: "nearata-related-discussions.generator",
      type: "select",
      label: app.translator.trans(
        "nearata-related-discussions.admin.settings.generator"
      ),
      options: {
        random: app.translator.trans(
          "nearata-related-discussions.admin.settings.generator_options.random"
        ),
        title: app.translator.trans(
          "nearata-related-discussions.admin.settings.generator_options.title"
        ),
      },
      default: "random",
      help: "",
    })
    .registerSetting({
      setting: "nearata-related-discussions.max-discussions",
      type: "number",
      label: app.translator.trans(
        "nearata-related-discussions.admin.settings.max_discussions"
      ),
      min: 1,
      help: app.translator.trans(
        "nearata-related-discussions.admin.settings.max_discussions_help"
      ),
    })
    .registerSetting({
      setting: "nearata-related-discussions.position",
      type: "select",
      label: app.translator.trans(
        "nearata-related-discussions.admin.settings.position"
      ),
      options: {
        first_post: app.translator.trans(
          "nearata-related-discussions.admin.settings.position_options.first_post"
        ),
        last_post: app.translator.trans(
          "nearata-related-discussions.admin.settings.position_options.last_post"
        ),
      },
      default: "first_post",
    })
    .registerSetting({
      setting: "nearata-related-discussions.cache",
      type: "text",
      label: app.translator.trans(
        "nearata-related-discussions.admin.settings.cache"
      ),
      help: app.translator.trans(
        "nearata-related-discussions.admin.settings.cache_help"
      ),
      placeholder: "0d0h0m",
    });
});
