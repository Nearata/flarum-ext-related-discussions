import app from 'flarum/admin/app';

app.initializers.add('nearata/related-discussions', () => {
  app.extensionData.for('nearata-related-discussions').registerSetting({
    setting: 'nearata-related-discussions.allow-guests',
    type: 'boolean',
    label: app.translator.trans('nearata-related-discussions.admin.settings.allow_guests'),
  });
});
