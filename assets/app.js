/*
 * Welcome to your app's main JavaScript file!
 */

// Import jQuery et ses plugins
import $ from 'jquery';
global.$ = global.jQuery = $;
window.$ = window.jQuery = $; // Exposer jQuery globalement pour les scripts en ligne

// Import des autres bibliothèques
import 'jquery-ui-dist/jquery-ui.min';
import 'select2';
import axios from 'axios';

// Import des styles
import './styles/app.scss';
import 'jquery-ui-dist/jquery-ui.min.css';
import 'select2/dist/css/select2.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import Bootstrap et l'exposer globalement
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap; // Exposer Bootstrap globalement

// Start the Stimulus application - COMMENTÉ TEMPORAIREMENT
// import './bootstrap';  

// Import flash messages component
import './js/components/flash-messages';

// Import navigation component
import './js/components/navigation';

// Initialisation manuelle du modal admin
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing admin modal...');
    
    // Sélectionner le bouton et le modal
    const adminButton = document.getElementById('adminButton');
    const adminModal = document.getElementById('adminModal');
    
    if (adminButton && adminModal) {
        console.log('Admin button and modal found, adding click handler');
        adminButton.addEventListener('click', function() {
            console.log('Admin button clicked, showing modal');
            try {
                const bsModal = new bootstrap.Modal(adminModal);
                bsModal.show();
            } catch (error) {
                console.error('Error showing modal:', error);
                // Fallback en cas d'erreur
                adminModal.classList.add('show');
                adminModal.style.display = 'block';
            }
        });
    } else {
        console.log('Admin button or modal not found');
        console.log('adminButton:', adminButton);
        console.log('adminModal:', adminModal);
    }
});