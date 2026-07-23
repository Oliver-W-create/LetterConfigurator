(function () {
    'use strict';

    function normalise(value) {
        return (value || '').toString().trim().toLocaleLowerCase();
    }

    function initSearch(root) {
        var input = root.querySelector('[data-lc-table-search]');
        var rows = Array.prototype.slice.call(root.querySelectorAll('tbody tr[data-lc-row]'));
        if (!input || !rows.length) return;
        input.addEventListener('input', function () {
            var query = normalise(input.value);
            rows.forEach(function (row) {
                row.style.display = !query || normalise(row.textContent).indexOf(query) !== -1 ? '' : 'none';
            });
        });
    }

    function initSort(root) {
        var body = root.querySelector('tbody');
        if (!body) return;
        root.querySelectorAll('th[data-lc-sort]').forEach(function (header) {
            header.addEventListener('click', function () {
                var index = Array.prototype.indexOf.call(header.parentNode.children, header);
                var direction = header.getAttribute('data-direction') === 'asc' ? 'desc' : 'asc';
                root.querySelectorAll('th[data-lc-sort]').forEach(function (item) { item.removeAttribute('data-direction'); });
                header.setAttribute('data-direction', direction);
                var rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-lc-row]'));
                rows.sort(function (a, b) {
                    var av = normalise(a.children[index] ? a.children[index].getAttribute('data-sort-value') || a.children[index].textContent : '');
                    var bv = normalise(b.children[index] ? b.children[index].getAttribute('data-sort-value') || b.children[index].textContent : '');
                    return direction === 'asc' ? av.localeCompare(bv, undefined, {numeric:true}) : bv.localeCompare(av, undefined, {numeric:true});
                });
                rows.forEach(function (row) { body.appendChild(row); });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-lc-table]').forEach(function (root) {
            initSearch(root);
            initSort(root);
        });
    });
}());
