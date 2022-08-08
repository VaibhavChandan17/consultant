<?
function hook_display_variant_plugin_alter(array &$definitions) {
  $definitions['full_page']['admin_label'] = t('Block layout');
}