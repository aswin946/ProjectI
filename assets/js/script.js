/*
 * assets/js/script.js
 * Small JavaScript used across the site.
 */

// Confirmation before destructive actions.
// Any <form data-confirm="Some question?"> asks the question before it is submitted.
document.addEventListener('submit', function (event) {
    var question = event.target.getAttribute('data-confirm');
    if (question && !window.confirm(question)) {
        event.preventDefault();   // user clicked Cancel: stop the form
    }
});

// Flash messages fade out by themselves after a few seconds (success messages only)
window.addEventListener('load', function () {
    var messages = document.querySelectorAll('.alert-success');
    messages.forEach(function (box) {
        setTimeout(function () { box.style.display = 'none'; }, 5000);
    });
});

// Transaction form: show only the categories that match the chosen type (Income / Expense).
// The server also checks this, so this code is only for convenience.
(function () {
    var typeSelect = document.getElementById('type');
    var categorySelect = document.getElementById('category_id');
    if (!typeSelect || !categorySelect) { return; }   // not on the transaction form

    function filterCategories() {
        var chosenType = typeSelect.value;
        Array.prototype.forEach.call(categorySelect.options, function (option) {
            if (option.value === '') { return; }      // keep "-- Select category --"
            var matches = option.getAttribute('data-type') === chosenType;
            option.disabled = !matches;
            option.hidden = !matches;
        });
        // If the selected category no longer matches, clear the selection
        var selected = categorySelect.options[categorySelect.selectedIndex];
        if (selected && selected.disabled) { categorySelect.value = ''; }
    }

    typeSelect.addEventListener('change', filterCategories);
    filterCategories();
})();
