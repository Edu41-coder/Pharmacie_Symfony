document.addEventListener('DOMContentLoaded', function() {
    // Pour le footer
    const toggleFooterBtn = document.getElementById('toggleFooter');
    const footer = document.querySelector('.footer');

    if (toggleFooterBtn && footer) {
        toggleFooterBtn.addEventListener('click', function() {
            footer.classList.toggle('hidden');
            localStorage.setItem('footerHidden', footer.classList.contains('hidden'));
        });

        const isFooterHidden = localStorage.getItem('footerHidden') === 'true';
        if (isFooterHidden) {
            footer.classList.add('hidden');
        }
    }

    // Pour le header
    const toggleHeaderBtn = document.getElementById('toggleHeader');
    const header = document.querySelector('.header');

    if (toggleHeaderBtn && header) {
        toggleHeaderBtn.addEventListener('click', function(e) {
            e.preventDefault();  // Empêche le comportement par défaut
            e.stopPropagation();  // Empêche la propagation
            header.classList.toggle('hidden');
            localStorage.setItem('headerHidden', header.classList.contains('hidden'));
        });

        // Restaure l'état au chargement
        const isHeaderHidden = localStorage.getItem('headerHidden') === 'true';
        if (isHeaderHidden) {
            header.classList.add('hidden');
        }
    }
}); 