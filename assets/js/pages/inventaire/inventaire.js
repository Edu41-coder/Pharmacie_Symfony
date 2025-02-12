import '../../../styles/pages/inventaire.scss';

// Initialisation de Select2 si nécessaire
import $ from 'jquery';
import 'select2';

$(document).ready(function() {
    // Initialisation de Select2 pour les selects avec la classe select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
}); 