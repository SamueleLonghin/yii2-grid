function cambiaPagina(e, url) {
    let t = $(e.target)[0];
    if (!$(t).is('A') && !$(t).is('SPAN') && !$(t).is('DIV') && !$(t).is('I') && !$(t).is('BUTTON') && !$(t).is('INPUT') && !$(t).is('TEXTAREA')) {
        window.location.href = url;
    }
}
