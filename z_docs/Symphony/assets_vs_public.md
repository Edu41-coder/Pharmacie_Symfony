# Différence entre les dossiers assets/js et public/js

## assets/js

-   **Traités par Webpack Encore**  : Ces fichiers sont compilés, optimisés et bundlés
-   **Permettent les fonctionnalités modernes**  : Support d'ES6+, import/export, etc.
-   **Transformation automatique**  : Transpilation via Babel, minification et optimisation
-   **Versionnement**  : Des hashes sont ajoutés aux fichiers pour l'invalidation de cache
-   **Inclusion**  : Via  `{{ encore_entry_script_tags('nom_entry') }}`
-   **Destination finale**  : Compilés vers  build

// Dans webpack.config.js

Encore.addEntry('app', './assets/js/app.js');

## public/js

-   **Fichiers statiques**  : Servis directement par le serveur web sans traitement
-   **JavaScript "tel quel"**  : Doivent être compatibles avec les navigateurs cibles
-   **Pas d'optimisation**  : Pas de minification ni de bundling automatiques
-   **Pas de versionnement**  : L'invalidation de cache doit être gérée manuellement
-   **Inclusion**  : Via  `<script src="{{ asset('js/mon-script.js') }}"></script>`
-   **Chemin direct**  : Accessibles via l'URL  `/js/mon-script.js`

## Quand utiliser chaque option ?

### Utilisez assets/js quand :

-   Vous avez besoin d'optimisation et de minification
-   Vous utilisez des fonctionnalités JS modernes
-   Vous développez des fonctionnalités complexes avec des dépendances
-   Vous avez besoin de bundling (regrouper plusieurs fichiers)

### Utilisez public/js quand :

-   Vous avez des scripts simples qui ne nécessitent pas de traitement
-   Vous intégrez des bibliothèques tierces précompilées
-   Vous développez rapidement sans avoir besoin de recompiler
-   Vous avez besoin d'accéder directement à un fichier spécifique

_**En règle générale, pour un projet Symfony moderne, l'approche recommandée est d'utiliser  js  et Webpack Encore pour la majorité de votre code JavaScript.**_
