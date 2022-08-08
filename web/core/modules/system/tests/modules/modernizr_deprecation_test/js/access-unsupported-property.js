/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/

(function (Drupal, once, Modernizr) {
  Drupal.behaviors.unsupportedModernizrProperty = {
    attach: function attach() {
      once('unsupported-modernizr-property', 'body').forEach(function () {
        var triggerDeprecationButton = document.createElement('button');
        triggerDeprecationButton.id = 'trigger-a-deprecation';
        triggerDeprecationButton.textContent = 'trigger a deprecation';
        triggerDeprecationButton.addEventListener('click', function () {
          var thisShouldTriggerWarning = Modernizr.touchevents;
        });
        document.querySelector('main').append(triggerDeprecationButton);
      });
    }
  };
})(Drupal, once, Modernizr);