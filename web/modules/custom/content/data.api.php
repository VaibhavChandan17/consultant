<?
function hook_form_alter(&$form, \Drupal\custom\content $form_state, $form_id) {
  if (isset($form['type']) && $form['type']['#value'] . '_node_settings' == $form_id) {
    $upload_enabled_types = \Drupal::config('mymodule.settings')
      ->get('upload_enabled_types');
    $form['workflow']['upload_' . $form['type']['#value']] = [
      '#type' => 'radios',
      '#title' => t('Attachments'),
      '#default_value' => in_array($form['type']['#value'], $upload_enabled_types) ? 1 : 0,
      '#options' => [
        t('Disabled'),
        t('Enabled'),
      ],
    ];

    // Add a custom submit handler to save the array of types back to the config file.
    $form['actions']['submit']['#submit'][] = 'mymodule_upload_enabled_types_submit';
  }
}