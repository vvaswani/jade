# Compatibility

## Compatibility with older browsers

* An HTML5 color input is used when creating labels, with a polyfill for older browsers. This requires testing on older desktop browsers, current mobile browsers and browsers without JavaScript.
* Bootstrap modals are used with AJAX for confirmation dialogs, with graceful fallback to regular confirmation pages for browsers without JavaScript. This requires testing on current mobile browsers and browsers without JavaScript.
* Hidden file inputs/file upload forms are triggered with JavaScript when links are clicked, with graceful fallback to regular confirmation pages for browsers without JavaScript. This requires testing on current mobile browsers and browsers without JavaScript.