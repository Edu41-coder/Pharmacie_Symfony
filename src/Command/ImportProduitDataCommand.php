<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class ImportProduitDataCommand extends Command
{
    protected static $defaultName = 'app:import-produits';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbPath = __DIR__ . '/../../var/data/data.db';
        $output->writeln("Connexion à la base de données SQLite : $dbPath");

        try {
            $pdo = new PDO("sqlite:$dbPath");
            
            // Vider la table avant l'import
            $pdo->exec('DELETE FROM produit');
            
            // Données des produits
            $produits = [
                [1, 'ACARBOSE BIOGARAN 100 mg', 'comprimé sécable.\r\nACARBOSE BIOGARAN est un antidiabétique', '13.87', 'oui', 65, null, 'non', 0],
                [2, 'ACEBUTOLOL ARROW 400 mg', 'comprimé pelliculé sécable \r\nHypertension artérielle.\r\n Traitement au long cours après infarctus du myocarde', '10.74', 'oui', 65, null, 'oui', 0],
                [4, 'ACECLOFENAC BIOGARAN 100 mg', 'comprimé pelliculé\r\nanti-inflammatoire non stéroïdien', '3.35', 'oui', 65, null, 'non', 0],
                [5, 'ACETATE DE CYPROTERONE SANDOZ 100 mg', 'comprimé sécable.\r\ncancer de la prostate ;', '70.44', 'oui', 100, 20, 'oui', 0],
                [6, 'ACETYLCYSTEINE EG 200 mg', 'poudre pour solution buvable en sachet-dose poudre pour solution buvable orale Autorisation active Procédure nationale Commercialisée 26/12/2003 EG LABO - LABORATOIRES EUROGENERICS Non\r\n69896678 ACETYLCYSTEINE EG LABO CONSEIL 200 mg SANS SUCRE, poudre pour solution.\r\npour brochite', '5.82', 'non', null, 20, 'non', 0],
                [7, 'ACETYLLEUCINE BIOGARAN 500 mg', 'comprimé.\r\ntraitement symptomatique de la crise vertigineuse.', '2.95', 'oui', 30, 20, 'non', 0],
                [8, 'ACICLOVIR ALMUS 200 mg', 'traitement ou la prévention de certaines formes de herpès', '8.18', 'oui', null, 65, 'oui', 0],
                [9, 'ACICLOVIR ALMUS 5 %', 'Manifestations de infections herpétiques génitales.\r\ncrème cutanée.', '6.87', 'oui', 65, null, 'non', 0],
                [10, 'ACIDE ACÉTYLSALICYLIQUE EG LABO CONSEIL 500 mg', 'indiqué en cas de douleurs d\'intensité légère à modérée et/ou de fièvre\r\ncomprimé', '3.04', 'non', null, null, 'oui', 0],
                [11, 'ACIDE ALENDRONIQUE BIOGARAN 70 mg', 'prévient la perte osseuse qui survient chez les femmes ménopausées\r\ncomprimé.', '8.84', 'oui', 65, null, 'non', 0],
                [12, 'ACIDE ALENDRONIQUE/VITAMINE D3 TEVA SANTE 70 mg/5600 UI,', 'comprimé\r\nboîte de 12', '12.84', 'oui', 65, null, 'non', 0],
                [13, 'ACIDE FOLIQUE ARROW 5 mg', null, '1.33', 'oui', 65, null, 'non', 0],
                [14, 'ACIDE FUSIDIQUE ARROW 2 %', 'crème cutanée', '1.76', 'oui', 30, null, 'non', 0],
                [15, 'ACIDE TIAPROFENIQUE ARROW 100 mg', 'comprimé sécable\r\nanti-inflammatoires non stéroïdiens', '3.92', 'non', null, null, 'non', 0],
                [16, 'ACIDE URSODESOXYCHOLIQUE ARROW 250 mg', 'comprimé pelliculé.\r\ninflammation de la vésicule biliaire,\r\n\r\ninfection ou obstruction des voies biliaires.', '6.83', 'oui', 65, null, 'non', 0],
                [17, 'DOLIPRANE 100 mg  poudre', 'poudre pour solution buvable en 12 sachet-dose', '2.45', 'non', 65, null, 'non', 0],
                [18, 'DOLIPRANE 100 mg suppositoire', '10 suppositoire sécable', '2.35', 'non', 65, null, 'non', 0],
                [19, 'DOLIPRANE 1000 mg comprimé', '8 comprimé', '2.18', 'non', 65, null, 'non', 0],
                [20, 'DOLIPRANE 1000 mg, comprimé effervescent', 'comprimé effervescent sécable', '2.18', 'non', 65, null, 'non', 0],
                [21, 'DOLIPRANE 1000 mg, gélule', '8 gélule orale', '2.18', 'non', 65, null, 'non', 0],
                [22, 'DOLIPRANE 1000 mg, poudre', 'poudre pour solution buvable en 8 sachet-dose', '2.25', 'non', 65, null, 'non', 0],
                [23, 'DOLIPRANE 2,4 POUR CENT', 'suspension buvable', '2.50', 'non', 65, null, 'non', 0],
                [24, 'DOLIPRANE 1000 mg suppositoire', '8suppositoire', '2.40', 'non', 65, null, 'non', 0],
                [25, 'DOLIPRANELIQUIZ 1000 mg', 'suspension buvable en sachet édulcoré', '3.00', 'oui', 65, null, 'non', 0],
                [26, 'DOLIPRANEVITAMINEC 500 mg/150 mg', '8 comprimé effervescent', '2.70', 'non', 65, null, 'non', 0],
                [27, 'DOLIRHUME PARACETAMOL ET PSEUDOEPHEDRINE 500 mg/30 mg,', 'comprimé', '2.00', 'non', 65, null, 'non', 0],
                [28, 'DORMICALM', 'comprimé enrobé\r\n Médicament traditionnel à base de plantes utilisé pour troubles du sommeil.', '7.84', 'non', null, null, 'non', 0],
                [29, 'ACTIQ 1200 microgrammes', 'Stupéfiant,comprimé avec applicateur buccal\r\n traitement des accès douloureux paroxystiques', '18.17', 'oui', 65, null, 'non', 0],
                [30, 'ACTISKENAN 10 mg', 'stupéfiant\r\ncomprimé orodispersible en boîte de 14 cp.', '2.26', 'oui', 65, null, 'non', 0],
                [31, 'CTISOUFRE 4 mg/50 mg', 'états inflammatoires chroniques des voies respiratoires', '7.80', 'non', null, null, 'oui', 0],
                [32, 'ACTONEL 75 mg', 'traitement de la maladie de Paget', '47.69', 'oui', 65, null, 'oui', 0],
                [33, 'ADEMPAS 2,5 mg', null, '25.78', 'oui', 65, null, 'oui', 0],
                [34, 'ADARTREL 2 mg', null, '24.00', 'oui', 65, null, 'non', 0],
                [35, 'ADOPORT 5 mg', null, '29.70', 'oui', 65, null, 'non', 0],
                [36, 'ALFUZOSINE EG L.P. 10 mg', 'traitement des troubles urinaires dus à un adénome de la prostate.', '9.78', 'oui', 65, null, 'non', 0],
                [37, 'ALGINATE DE SODIUM/BICARBONATE DE SODIUM SANDOZ 500 mg/267 mg', 'suspension buvable en sachet\r\nreflux gastro-oesophagien', '4.01', 'non', null, null, 'oui', 0],
                [38, 'ALLOPURINOL BIOGARAN 300 mg', 'comprimé\r\nIl est utilisé pour traiter les excès d\'acide urique lorsqu\'ils sont responsables de goutte ou de calculs rénaux et pour prévenir ainsi ces maladies.', '3.06', 'oui', 65, null, 'non', 0],
                [39, 'ALMOTRIPTAN TEVA 12,5 mg', 'comprimé pelliculé\r\nsoulager les maux de tête associés aux crises de migraine', '13.11', 'oui', 65, null, 'non', 0],
                [40, 'ALPRAZOLAM ARROW 0,50 mg', 'comprimé sécable\r\nanxiolytique', '2.25', 'oui', 65, null, 'non', 0],
                [41, 'AMBRISENTAN TEVA 10 mg', 'traiter l\'hypertension artérielle pulmonaire', '10.20', 'oui', 65, null, 'non', 0],
                [42, 'AMBROXOL BIOGARAN CONSEIL 30 mg', 'comprimé sécable\r\nexpectorant', '3.99', 'non', null, null, 'non', 0],
                [43, 'AMIODARONE BIOGARAN 200 mg', 'comprimé sécable\r\nantiarythmique', '8.22', 'oui', 65, null, 'non', 0],
                [44, 'AMISULPRIDE BIOGARAN 200 mg', 'comprimé sécable\r\nantipsychotique', '37.22', 'oui', 65, null, 'non', 0],
                [45, 'AMITRIPTYLINE SUBSTIPHARM 40 mg/mL', 'solution buvable en gouttes\r\nantidépresseur tricyclique', '4.17', 'oui', 65, null, 'non', 0],
                [46, 'AMLODIPINE ARROW 10 mg', 'gélule orale\r\nhypertension', '10.13', 'oui', 65, null, 'oui', 0],
                [47, 'AMOROLFINE SUBSTIPHARM 5 %', 'vernis à ongles médicamenteux', '9.50', 'oui', 65, null, 'non', 0],
                [48, 'AMOXICILLINE ARROW 500 mg', 'gélule orale', '8.50', 'oui', 65, null, 'non', 0],
                [49, 'AMOXICILLINE ARROW 250 mg/5 mL', 'poudre pour suspension buvable', '9.50', 'oui', 65, null, 'non', 0],
                [50, 'AMOXICILLINE/ACIDE CLAVULANIQUE EG 500 mg/62,5 mg', 'comprimé pelliculé', '9.50', 'oui', 65, null, 'non', 0],
                [51, 'AMOXICILLINE/ACIDE CLAVULANIQUE BIOGARAN 100 mg/12,50 mg', 'par ml NOURRISSONS, poudre pour suspension buvable en flacon', '7.80', 'oui', 65, null, 'non', 0],
                [52, 'AMOXICILLINE/ACIDE CLAVULANIQUE TEVA 1 g/ 125 mg ADULTES', 'poudre pour suspension buvable en sachet-dose', '9.50', 'oui', 65, null, 'non', 0],
                [53, 'ANAFRANIL 75 mg', 'comprimé pelliculé sécable', '15.78', 'oui', 65, null, 'non', 0],
                [54, 'ANAGRELIDE SANDOZ 0,5 mg', 'gélule', '10.48', 'oui', 65, null, 'oui', 0],
                [55, 'ANASTROZOLE EG 1 mg', 'Traitement du cancer du sein', '85.74', 'oui', 100, null, 'oui', 0],
                [56, 'ANDROCUR 50 mg', 'comprimé sécable', '22.52', 'oui', 65, null, 'non', 0],
                [57, 'ANTARENE 200 mg', 'comprimé pelliculé', '5.60', 'oui', 65, null, 'non', 0],
                [58, 'APREPITANT ARROW 125 mg', 'gélule', '11.20', 'non', 65, null, 'non', 0],
                [59, 'APROVEL 150 mg', 'comprimé pelliculé', '18.40', 'oui', 65, null, 'non', 0],
                [60, 'AQUA MARINA BOIRON', 'degré de dilution compris entre 2CH et 30CH', '5.40', 'non', null, null, 'non', 0],
                [61, 'ARALIA RACEMOSA LEHNING', 'degré de dilution compris entre 2CH et 30CH', '4.80', 'non', null, null, 'non', 0],
                [62, 'ARANESP 150 microgrammes', 'solution injectable en seringue préremplie', '6.20', 'non', null, null, 'non', 0],
                [63, 'ARBUTUS UNEDO BOIRON', 'degré de dilution compris entre 2CH et 30CH', '3.80', 'non', null, null, 'non', 0],
                [64, 'ARCALION 200 mg', 'comprimé enrobé', '10.50', 'oui', 65, null, 'non', 0],
                [65, 'ARGENTUM NITRICUM LEHNING', 'degré de dilution compris entre 2CH et 30CH', '3.50', 'oui', null, null, 'non', 0],
                [66, 'ARIPIPRAZOLE ALMUS 15 mg', 'comprimé', '3.40', 'non', null, null, 'non', 0],
                [67, 'ARNICA MONTANA TEINTURE MERE BOIRON', 'liquide pour application cutanée', '6.30', 'non', null, null, 'non', 0],
                [68, 'ARNICALME', 'comprimé orodispersible', '4.70', 'non', null, null, 'non', 0],
                [69, 'ARNITROSIUM', 'comprimé sublingual', '3.85', 'non', null, null, 'non', 0],
                [70, 'ARTHRODONT 1 POUR CENT', 'pâte gingivale', '5.80', 'non', null, null, 'non', 0],
                [71, 'ASCABIOL 10 %', 'émulsion pour application cutanée', '3.90', 'oui', null, null, 'non', 0],
                [72, 'ASPEGIC 500 mg', 'poudre pour solution buvable', '4.80', 'non', null, null, 'non', 0],
                [73, 'ASPIRINE UPSA VITAMINEE C TAMPONNEE EFFERVESCENTE', 'comprimé effervescent', '5.71', 'oui', null, null, 'non', 0],
                [74, 'ATAZANAVIR BIOGARAN 300 mg', 'gélule', '18.50', 'oui', 65, null, 'oui', 0],
                [75, 'ATENOLOL ARROW 100 mg', 'comprimé pelliculé sécable', '10.75', 'oui', null, null, 'non', 0],
                [76, 'AGOMELATINE BIOGARAN 25 mg', 'comprimé pelliculé', '5.50', 'oui', 65, null, 'non', 0],
                [77, 'MERCRYL SOLUTION MOUSSANTE', 'solution pour application cutanée', '7.80', 'non', null, null, 'non', 0]
            ];

            // Préparation de la requête d'insertion
            $stmt = $pdo->prepare("INSERT INTO produit (produit_id, nom, description, prix_vente_ht, prescription, taux_remboursement, alerte, declencher_alerte, is_deleted) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Insertion des données
            $pdo->beginTransaction();
            
            foreach ($produits as $produit) {
                $stmt->execute($produit);
                $output->writeln("Produit importé : " . $produit[1]);
            }

            $pdo->commit();
            $output->writeln("Données importées avec succès !");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $output->writeln("Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 