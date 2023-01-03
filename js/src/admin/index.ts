import app from 'flarum/admin/app';

app.initializers.add('nearata/related-discussions', () => {
  app.extensionData
    .for('nearata-related-discussions')
    .registerSetting({
      setting: 'nearata-related-discussions.allow-guests',
      type: 'boolean',
      label: app.translator.trans('nearata-related-discussions.admin.settings.allow_guests'),
    })
    .registerSetting({
      setting: 'nearata-related-discussions.algorithm',
      type: 'select',
      label: app.translator.trans('nearata-related-discussions.admin.settings.algorithm'),
      options: {
        random: app.translator.trans('nearata-related-discussions.admin.settings.algorithm_options.random'),
      },
      default: 'random',
      help: '',
    })
    .registerSetting({
      setting: 'nearata-related-discussions.max-discussions',
      type: 'number',
      label: app.translator.trans('nearata-related-discussions.admin.settings.max_discussions'),
      min: 1,
      help: app.translator.trans('nearata-related-discussions.admin.settings.max_discussions_help'),
    });
});
