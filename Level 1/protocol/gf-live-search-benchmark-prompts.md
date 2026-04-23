# GF Live Search — Prompts de Benchmark LLM

Deux niveaux de prompt pour évaluer la capacité des modèles à recréer le plugin **GF Live Search** (v1.0.2).

---

## Contexte du benchmark

Le plugin de référence ajoute une recherche en temps réel (filtrage côté client, sans rechargement de page) à la liste des formulaires Gravity Forms dans l'administration WordPress. Il est composé de trois fichiers principaux : `gf-live-search.php`, `assets/gf-live-search.js`, `assets/gf-live-search.css`.

---

## Prompt Niveau 1 — Minimal

> Crée un plugin WordPress nommé **GF Live Search** qui ajoute une recherche en temps réel à la page liste des formulaires Gravity Forms (`/wp-admin/?page=gf_edit_forms`).
>
> Le plugin doit :
> - Filtrer instantanément les lignes du tableau sans rechargement de page
> - Ne charger ses assets que sur la page concernée
> - Respecter les bonnes pratiques WordPress (hooks, sécurité, internationalisation)
>
> Structure attendue :
> ```
> gf-live-search/
> ├── gf-live-search.php
> └── assets/
>     ├── gf-live-search.js
>     └── gf-live-search.css
> ```

**Ce que ce prompt teste :** La capacité d'inférence. Un bon modèle doit deviner les détails implicites (DOM GF, debounce, empty state, etc.). Un modèle faible produira une implémentation naïve (AJAX au lieu de DOM, jQuery old-school, pas de sanitization).

---

## Prompt Niveau 2 — Détaillé

> Crée un plugin WordPress nommé **GF Live Search** qui ajoute une recherche en temps réel à la page liste des formulaires Gravity Forms.
>
> ### Structure de fichiers
>
> ```
> gf-live-search/
> ├── gf-live-search.php          ← Fichier principal du plugin
> └── assets/
>     ├── gf-live-search.js       ← Logique de filtrage
>     └── gf-live-search.css      ← Styles admin
> ```
>
> ---
>
> ### `gf-live-search.php` — Fichier principal
>
> **En-tête du plugin :**
> ```
> Plugin Name:  GF Live Search
> Plugin URI:   https://github.com/guilamu/gf-live-search
> Description:  Adds live filtering to the Gravity Forms forms list. As you type in the search box, forms are instantly filtered without a page reload.
> Version:      1.0.0
> Requires at least: 5.8
> Requires PHP: 7.4
> Author:       Ton Nom
> License:      GPL-2.0-or-later
> Text Domain:  gf-live-search
> Domain Path:  /languages
> ```
>
> **Architecture :** Classe singleton `GF_Live_Search` instanciée via `GF_Live_Search::get_instance()`.
>
> **Hooks enregistrés dans le constructeur :**
> - `plugins_loaded` → `load_textdomain()` — charge les traductions depuis `languages/`
> - `admin_enqueue_scripts` → `enqueue_assets( string $hook_suffix )` — charge les assets conditionnellement
>
> **Méthode `enqueue_assets()` — logique conditionnelle stricte :**
>
> Ne charger les assets **que si toutes ces conditions sont vraies simultanément** :
> 1. `get_current_screen()` existe et retourne un objet avec `id === 'toplevel_page_gf_edit_forms'`
> 2. `$_GET['page']` (après `sanitize_key` + `wp_unslash`) vaut exactement `'gf_edit_forms'`
> 3. `$_GET['id']` est absent **ou** vaut `0` (on est sur la liste, pas sur l'éditeur d'un formulaire spécifique)
>
> Si une condition échoue → `return` immédiatement, ne rien enqueuer.
>
> **Enqueue du JS :**
> ```php
> wp_enqueue_script(
>     'gf-live-search',
>     GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.js',
>     [ 'wp-i18n' ],        // dépendance : wp-i18n pour les traductions JS
>     GF_LIVE_SEARCH_VERSION,
>     true                  // in footer
> );
> ```
>
> **Inline script de traductions (avant le script principal) :**
> ```php
> wp_add_inline_script(
>     'gf-live-search',
>     'window.gfLiveSearchI18n = ' . wp_json_encode( $this->get_script_translations() ) . ';',
>     'before'
> );
> ```
>
> Si `wp_set_script_translations()` existe, l'appeler aussi :
> ```php
> wp_set_script_translations( 'gf-live-search', 'gf-live-search', GF_LIVE_SEARCH_PLUGIN_DIR . 'languages' );
> ```
>
> **Enqueue du CSS :**
> ```php
> wp_enqueue_style(
>     'gf-live-search',
>     GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.css',
>     [],
>     GF_LIVE_SEARCH_VERSION
> );
> ```
>
> **Méthode privée `get_script_translations() : array` :**
> Retourne les chaînes traduisibles passées au JS via `window.gfLiveSearchI18n` :
> ```php
> return [
>     'strings' => [
>         'No forms match your search.' => __( 'No forms match your search.', 'gf-live-search' ),
>         'Ctrl/Cmd+F to focus'         => __( 'Ctrl/Cmd+F to focus', 'gf-live-search' ),
>     ],
>     'plurals' => [
>         '%d form' => [
>             _n( '%d form', '%d forms', 1, 'gf-live-search' ),
>             _n( '%d form', '%d forms', 2, 'gf-live-search' ),
>         ],
>     ],
> ];
> ```
>
> ---
>
> ### `assets/gf-live-search.js` — Logique de filtrage
>
> **Environnement cible :** IIFE `(function(){ 'use strict'; ... })()`, pas de transpileur, ES5+ compatible, pas de jQuery. Toutes les variables en `var`.
>
> #### Structure du DOM Gravity Forms ciblé
>
> ```html
> <form id="form_list_search" method="get" action="">
>   <div class="search-box">
>     <input name="s" type="search" value="" />
>     <!-- ou type="text" selon la version de GF -->
>   </div>
> </form>
>
> <table class="wp-list-table widefat">
>   <thead>...</thead>
>   <tbody id="the-list">
>     <tr class="gf_form_list" id="form-row-1">
>       <td class="column-title">Nom du formulaire</td>
>       <td class="column-form_id">1</td>
>       <td class="column-entries">42</td>
>     </tr>
>     <!-- autres lignes -->
>   </tbody>
> </table>
>
> <div class="tablenav">
>   <span class="displaying-num">5 forms</span>
>   <div class="tablenav-pages">
>     <span class="pagination-links">...</span>
>     <input class="current-page" type="number" value="1" />
>   </div>
> </div>
> ```
>
> #### Fonctions utilitaires à implémenter
>
> **`debounce(fn, delay)`** — Retourne une version debouncée de `fn` qui se déclenche après `delay` ms de silence. Utiliser `clearTimeout` + `setTimeout`.
>
> **`normalize(str)`** — Normalisation pour comparaison insensible à la casse ET aux diacritiques :
> ```js
> return str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
> ```
>
> **`toArray(list)`** — Convertit un HTMLCollection/NodeList en tableau via `Array.prototype.slice.call`.
>
> **`getColumnCount(tableElement)`** — Compte le nombre total de colonnes (header cells), en tenant compte des attributs `colspan`.
>
> **`getFormRows(container)`** — Retourne les `<tr>` représentant des formulaires (filtrer : `tagName === 'TR'`, exclure `id === 'gf-live-search-no-results'`).
>
> **`removeMatches(root, selector)`** — Supprime du clone les noeuds `.row-actions` et `.screen-reader-text` pour qu'ils n'interfèrent pas avec la recherche.
>
> **`getRowSearchText(row)`** — Clone la ligne, appelle `removeMatches` sur le clone, retourne `normalize(clone.textContent)`. Texte mis en cache dans `row.dataset.gflsSearchText` via `primeRow(row)`.
>
> #### Internationalisation JS
>
> Lire `window.gfLiveSearchI18n` (injecté par PHP). Implémenter les fallbacks :
> - `__(text, domain)` : chercher d'abord dans `i18nData.strings`, puis `window.wp.i18n.__`, puis retourner `text`
> - `_n(singular, plural, number, domain)` : chercher d'abord dans `i18nData.plurals`, puis `window.wp.i18n._n`, puis retour basique
> - `sprintf` : utiliser `window.wp.i18n.sprintf` si disponible, sinon `format.replace('%d', value)`
>
> #### Logique principale (dans `DOMContentLoaded`)
>
> **Initialisation :**
> 1. Récupérer `form#form_list_search` → si absent, `return` (pas sur la bonne page)
> 2. Récupérer l'input : `form.querySelector('input[name="s"], input[type="search"], input[type="text"]')`
> 3. Récupérer `tbody#the-list` → si absent, `return`
> 4. Récupérer la table via `tbody.closest('table')`
> 5. Lire la page courante (`getCurrentPageNumber`) et le total de pages (`getTotalPages`)
> 6. Initialiser `currentRows` = `getFormRows(tbody).map(primeRow)` et `remoteRows = []`
>
> **Restructuration du DOM autour de l'input :**
> Créer un `<span class="gf-live-search-input-wrap">` et envelopper l'input dedans. Ajouter la classe `gf-live-search-search-box` au `.search-box` parent. Ajouter un `<span class="gf-live-search-shortcut-hint" aria-hidden="true">Ctrl/Cmd+F to focus</span>` **à l'intérieur** du `inputWrap`, après l'input.
>
> **Row "no results" :**
> Créer un `<tr id="gf-live-search-no-results">` contenant une `<td>` avec :
> - `colSpan` = résultat de `getColumnCount(table)`
> - `className = 'gf-live-search-empty'`
> - Un `<span class="gf-live-search-empty-icon" aria-hidden="true">` contenant l'emoji `🔍` (U+1F50D)
> - Un `<span>` contenant le texte traduit `'No forms match your search.'`
>
> L'insérer à la fin du `tbody` avec `hidden = true`.
>
> **Fonction `syncInputState()`** : toggle la classe `gf-live-search-has-value` sur `#form_list_search` selon `input.value.trim() !== ''`.
>
> **Fonction `preloadOtherPages()`** — Précharge les pages paginées restantes en arrière-plan :
> - Si `totalPages <= 1` ou si `fetch`/`DOMParser`/`URL` sont absents → `Promise.resolve(remoteRows)`
> - Sinon, pour chaque page ≠ `currentPage` : `fetch(getPageUrl(pageNumber), { credentials: 'same-origin' })`, parser la réponse HTML avec `DOMParser`, extraire `#the-list`, importer les lignes avec `document.importNode(row, true)`, les initialiser avec `primeRow`, les marquer `hidden = true`, les insérer dans le `tbody` avant la ligne "no results", et les pousser dans `remoteRows`
> - Si `input.value.trim() !== ''` après le chargement → relancer `filterForms()`
>
> **Fonction `filterForms()`** :
> 1. `query = normalize(input.value.trim())`
> 2. Itérer sur `currentRows.concat(remoteRows)`
> 3. Si `query` vide : afficher toutes les `currentRows`, masquer toutes les `remoteRows`, masquer la ligne "no results", appeler `updateCountBadge(0, query)`, `syncInputState()`, `return`
> 4. Sinon : pour chaque ligne, comparer `row.dataset.gflsSearchText.indexOf(query) !== -1`. `row.hidden = !matches`. Incrémenter `visibleCount` si match
> 5. `noResults.hidden = visibleCount !== 0`
> 6. Appeler `updateCountBadge(visibleCount, query)` et `syncInputState()`
>
> **Fonction `updateCountBadge(count, query)`** — Met à jour le/les `.displaying-num` de GF :
> - Si `query` vide : restaurer `badge.dataset.gflsOriginal` si présent, puis `return`
> - Sinon : sauvegarder le texte original dans `badge.dataset.gflsOriginal` (si pas encore fait), puis remplacer par `sprintf(_n('%d form', '%d forms', count, 'gf-live-search'), count)`
>
> **Suppression du rechargement sur Entrée :**
> ```js
> input.addEventListener('keydown', function(e) {
>     if (e.key === 'Enter' && input.value.trim() !== '') {
>         e.preventDefault();
>     }
> });
> ```
>
> **Debounce + wiring :**
> ```js
> var debouncedFilter = debounce(filterForms, 150);
> input.addEventListener('input', debouncedFilter);
> input.addEventListener('change', filterForms); // autofill
> ```
>
> **Initialisation finale :**
> ```js
> syncInputState();
> preloadOtherPages();
> if (input.value.trim() !== '') filterForms(); // valeur pré-remplie (ex: ?s=foo dans l'URL)
> ```
>
> **Raccourcis clavier :**
> Écouter `document.addEventListener('keydown', ...)` :
> - Touche `/` (hors champ éditable) → `e.preventDefault()` + `input.focus()` + `input.select()`
> - `Ctrl/Cmd+F` (hors champ éditable) → `e.preventDefault()` + `input.focus()` + `input.select()`
> - Ne rien faire si `activeElement` est un `INPUT`, `TEXTAREA`, `SELECT`, ou `contenteditable`
>
> ---
>
> ### `assets/gf-live-search.css` — Styles admin
>
> Le CSS doit s'intégrer à l'esthétique WP admin sans surcharger l'interface. Aucune dépendance externe.
>
> **Row "no results" (`#gf-live-search-no-results td.gf-live-search-empty`) :**
> - `padding: 32px 16px`, `text-align: center`
> - `color: #6c7176` (WP admin muted text), `font-size: 14px`
> - `background: #fff`, `border-bottom: none`
>
> **Icône "no results" (`.gf-live-search-empty-icon`) :**
> - `display: block`, `font-size: 28px`, `margin-bottom: 8px`, `opacity: 0.6`
>
> **Input actif (a une valeur) :**
> ```css
> #form_list_search.gf-live-search-has-value input[name="s"],
> #form_list_search.gf-live-search-has-value input[type="search"],
> #form_list_search.gf-live-search-has-value input[type="text"] {
>     border-color: #2271b1;
>     box-shadow: 0 0 0 1px #2271b1;
>     outline: none;
> }
> ```
>
> **Wrapper search-box :**
> ```css
> #form_list_search .gf-live-search-search-box { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
> #form_list_search .gf-live-search-input-wrap { position: relative; display: inline-flex; align-items: center; }
> ```
>
> **Hint raccourci clavier (`.gf-live-search-shortcut-hint`) :**
> ```css
> position: absolute; left: 8px; top: 50%; transform: translateY(-50%);
> font-size: 11px; color: #a7aaad; pointer-events: none; white-space: nowrap;
> ```
> Masquer quand focus ou valeur présente :
> ```css
> #form_list_search:focus-within .gf-live-search-shortcut-hint,
> #form_list_search.gf-live-search-has-value .gf-live-search-shortcut-hint { display: none; }
> ```
>
> **Masquer la pagination quand un filtre est actif :**
> ```css
> #form_list_search.gf-live-search-has-value .tablenav-pages .pagination-links { display: none; }
> ```
>
> ---
>
> ### Contraintes et interdictions
>
> - **Pas de jQuery** dans le JS (même si WordPress le charge, ne pas l'utiliser)
> - **Pas d'appel AJAX** vers le serveur pour filtrer — tout est côté client
> - **Pas de modification** des fichiers ou de la base de données de Gravity Forms
> - **Pas de dépendances NPM** — le JS doit fonctionner tel quel sans build step
> - **ES5+** — utiliser `var`, pas `const`/`let`, pas de classes, pas d'arrow functions (compatibilité maximale)
> - Le script ne doit **jamais** se charger hors de la page liste GF (vérification côté PHP ET côté JS)
> - Respecter les WordPress Coding Standards pour le PHP

---

## Grille de scoring

| Critère | Pts | Ce qui est vérifié |
|---|---|---|
| **Activation sans fatal error** | 15 | `plugin_check` ou activation manuelle |
| **Filtrage DOM fonctionnel** | 20 | Saisir dans le champ → lignes masquées/affichées |
| **Condition de chargement stricte** | 10 | Assets absents sur toutes les autres pages admin |
| **Row "no results"** | 10 | Apparaît quand 0 résultat, disparaît sinon |
| **Raccourcis `/` et `Ctrl+F`** | 10 | Focus input via clavier |
| **updateCountBadge** | 10 | Le compteur `.displaying-num` mis à jour |
| **Préchargement pages paginées** | 10 | `preloadOtherPages()` avec `fetch` + `DOMParser` |
| **Diacritiques + casse** | 5 | `é` trouve `e`, `É` trouve `é` |
| **Internationalisation** | 5 | `gf-live-search.pot` générable, `wp_set_script_translations` |
| **Qualité code PHP** | 5 | Nonces non requis (lecture seule), singleton, `defined('ABSPATH')` |
| **Total** | **100** | |

---

## Notes pour l'évaluation

### Discriminants clés entre Niveau 1 et Niveau 2

Les modèles qui produisent un code quasi-identique avec les deux niveaux de prompt sont ceux qui *inférent correctement* les détails implicites — c'est un signal fort de compréhension du domaine WordPress/GF.

### Pièges révélateurs

Un modèle **faible** fera typiquement :
- AJAX côté serveur au lieu de DOM filtering
- `jQuery(document).ready(...)` au lieu de l'IIFE vanilla
- Chargement des assets sur toutes les pages admin (pas de condition `gf_edit_forms`)
- Input créé from scratch au lieu d'intercepter le champ natif de GF
- Oublier le cas pagination multiple (`preloadOtherPages`)

Un modèle **fort** fera :
- Intercepter l'input existant `#form_list_search input[name="s"]`
- Debounce à 150ms
- Normalisation NFD pour les diacritiques
- Mise en cache du texte de recherche par ligne (`dataset.gflsSearchText`)
- Gestion propre du compteur `.displaying-num` avec sauvegarde/restauration

### Variation anti-mémorisation recommandée

Pour s'assurer que le modèle raisonne et ne récite pas un plugin existant, ajouter une contrainte inédite dans les deux niveaux :

> *"Ajouter un badge en temps réel à côté du champ de recherche indiquant le nombre de formulaires correspondants, ex : `[3 / 12]` (résultats / total)."*

Cette fonctionnalité n'existe pas dans le plugin de référence, ce qui rend impossible toute récitation mémorisée.

