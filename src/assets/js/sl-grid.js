/*function cambiaPagina(e, url) {
    let t = $(e.target)[0];
    if (!$(t).is('A') && !$(t).is('SPAN') && !$(t).is('DIV') && !$(t).is('I') && !$(t).is('BUTTON') && !$(t).is('INPUT') && !$(t).is('TEXTAREA')) {
        window.location.href = url;
    }
}
*/


// Intercetta i clic sui link di ordinamento della GridView e aggiungi il parametro del tab corrente all\'URL
$(document).on("click", ".sml-table-header th a[data-sort], .page-link", function (e) {
    e.preventDefault();
    var tabId = window.location.hash.substr(1);
    var url = new URL($(this).attr("href"), window.location.origin);
    url.hash = tabId;
    window.location.href = url.toString();
});


$(document).ready(function () {
    $('.sl-gridview-clickable-row').each(function () {
        const url = $(this).data('url');
        $(this).find('td').each(function () {
            // Controlla se la cella contiene già un link
            const existingLink = $(this).find('a, button, input, select, textarea');
            if (existingLink.length === 0) {
                $(this).wrapInner('<a href=\"' + url + '\" class=\"stretched-link\"></a>');
            } else {
                existingLink.addClass('sl-gridview-existing-link');
            }
        });
    });
});
