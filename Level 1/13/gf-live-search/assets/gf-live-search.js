jQuery(document).ready(function($) {

    // On attend que l'API et le DOM soient prêts
    const $targetTableBody = $('#gf_edit_forms_table_body'); // Cibler le tbody du tableau de GF (peut varier)
    const searchFieldId = 'gfls-search-input'; // ID pour notre champ de recherche

    // 1. Création et injection du champ de recherche au-dessus du tableau
    function injectSearchInput() {
        if ($('#' + searchFieldId).length > 0) return; // Prévention des injections multiples

        const $searchWrapper = $('<div id="gfls-container" style="margin-bottom: 15px;">');
        $searchWrapper.append('<label for="' + searchFieldId + '">Recherche de Formulaires :</label>');
        $searchWrapper.append('<input type="text" id="' + searchFieldId + '" placeholder="Nom, ID ou Description..." style="width: 100%;">');
        
        // Injection juste avant le tableau (ou là où il devrait se trouver)
        $('.admin-page-content').prepend($searchWrapper); 
    }

    // Assurez-vous que l'input existe avant d'ajouter l'écouteur
    injectSearchInput();


    /**
     * Fonction principale de filtrage.
     * @param {string} filterValue La valeur entrée par l'utilisateur.
     */
    function filterForms(filterValue) {
        const query = filterValue.toLowerCase().trim();

        if (!query || $targetTableBody.length === 0) {
            // Si vide ou pas de tableau, on affiche tout
            $targetTableBody.find('tr').show();
            return;
        }

        // Récupère toutes les lignes du corps du tableau
        const rows = $targetTableBody.find('tr');

        rows.each(function() {
            const row = $(this);
            let matches = false;

            // 2. Logique de filtrage : On va vérifier le contenu textuel global de la ligne.
            // Pour une performance optimale, il faudrait cibler des colonnes spécifiques (ex: titre).
            // Ici, on utilise .text() pour la simplicité et l'exhaustivité.
            const rowText = row.text().toLowerCase();

            if (rowText.includes(query)) {
                matches = true;
            }

            // 3. Affichage/Masquage de la ligne
            if (matches) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // 4. Ajout de l'écouteur d'événements
    const $searchInput = $('#' + searchFieldId);

    if ($searchInput.length) {
         $searchInput.on('keyup', function() {
            const value = $(this).val();
            filterForms(value);
        });

        // Bonus : Gérer le focus/blur pour réinitialiser si nécessaire
        $searchInput.on('focus', function() {
            // Optionnel: On peut aussi vouloir déclencher un "reset" de la recherche ici
        });
    }
});
