/* Un œil sur chaque champ mot de passe du site : toucher l'œil affiche ou masque ce qui est tapé.
   Chargé par les gabarits du site public et de l'admin ; dans un groupe AdminLTE, l'œil remplace le cadenas. */
(function () {
    'use strict';

    const EYE = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    const EYE_OFF = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    const css = document.createElement('style');
    css.textContent =
        '.pw-wrap{position:relative;display:block}' +
        '.pw-wrap>input{padding-right:2.9rem}' +
        '.pw-eye{position:absolute;top:0;right:0;bottom:0;width:2.75rem;display:flex;align-items:center;justify-content:center;' +
        'border:0;background:transparent;color:#6c757d;cursor:pointer;padding:0}' +
        '.pw-eye:hover,.pw-eye:focus-visible{color:#212529}' +
        '.input-group-text.pw-eye-text{padding:0}' +
        '.input-group-text .pw-eye{position:static;width:2.6rem;height:100%}';
    document.head.appendChild(css);

    function makeButton(input) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'pw-eye';
        const sync = () => {
            const shown = input.type === 'text';
            b.innerHTML = shown ? EYE_OFF : EYE;
            b.setAttribute('aria-label', shown ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            b.title = b.getAttribute('aria-label');
        };
        b.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            sync();
            input.focus();
        });
        sync();
        // Toujours renvoyé masqué, pour que le navigateur propose de l'enregistrer comme un mot de passe
        if (input.form) input.form.addEventListener('submit', () => { input.type = 'password'; sync(); });
        return b;
    }

    function enhance(input) {
        if (input.dataset.pwEye) return;
        input.dataset.pwEye = '1';
        const next = input.nextElementSibling;
        // Admin : le cadenas à droite du champ devient l'œil
        if (input.parentElement.classList.contains('input-group') && next && next.classList.contains('input-group-append')) {
            const box = next.querySelector('.input-group-text');
            if (box) {
                box.classList.add('pw-eye-text');
                box.innerHTML = '';
                box.appendChild(makeButton(input));
                return;
            }
        }
        const wrap = document.createElement('span');
        wrap.className = 'pw-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        wrap.appendChild(makeButton(input));
    }

    function run() { document.querySelectorAll('input[type="password"]').forEach(enhance); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run); else run();
})();
