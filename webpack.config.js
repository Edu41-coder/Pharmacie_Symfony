const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .addEntry('produits', './assets/js/pages/produits/index.js')
    .addEntry('inventaire', './assets/js/pages/inventaire/inventaire.js')
    .addEntry('ventes', './assets/js/pages/ventes.js')
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    
    // enables Sass/SCSS support
    .enableSassLoader()
    
    // Copier les images
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery'
    })
;

module.exports = Encore.getWebpackConfig();