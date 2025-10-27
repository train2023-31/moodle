define([], function() {
    // Check if already loaded globally
    if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
        console.error('Select2 is not available globally. Please include select2.full.min.js in the page.');
    }
    return window.jQuery.fn.select2;
});

