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

// Import flash messages component
import './js/components/flash-messages';

// Import navigation component
import './js/components/navigation';

