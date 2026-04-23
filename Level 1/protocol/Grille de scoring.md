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