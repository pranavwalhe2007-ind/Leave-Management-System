document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    if (sidebar && toggle) {
        toggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });
    }
    document.querySelectorAll('[data-date-start], [data-date-end]').forEach(function (input) {
        input.addEventListener('change', function () {
            const start = document.querySelector('[data-date-start]')?.value;
            const end = document.querySelector('[data-date-end]')?.value;
            const output = document.querySelector('[data-days-output]');
            if (start && end && output) {
                const difference = Math.round((new Date(end) - new Date(start)) / 86400000) + 1;
                output.textContent = difference > 0 ? difference + (difference === 1 ? ' day' : ' days') : 'Select valid dates';
            }
        });
    });
});
