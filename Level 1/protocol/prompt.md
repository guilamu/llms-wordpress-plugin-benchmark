La question clé d'abord : **qui fait quoi ?**

- **Toi** → tu soumets le prompt à chaque modèle, tu récupères le code
- **Un agent IA** → il score chaque rendu contre la grille
- **Le plugin de référence** → c'est le "corrigé"

***

## Workflow complet

### Étape 1 — Préparer l'environnement de scoring

Donne à ton agent scoreur les trois fichiers de référence **une seule fois au départ** :
- `gf-live-search.php`
- `assets/gf-live-search.js`
- `assets/gf-live-search.css`
- La grille de scoring du document benchmark

Prompt système de l'agent :
> *"Tu es un évaluateur de code. Tu reçois des implémentations de plugins WordPress à scorer. Pour chaque soumission, applique la grille fournie et retourne un JSON structuré avec le score par critère, le score total, et une justification courte par critère."*

***

### Étape 2 — Soumettre le prompt à chaque modèle

**Le prompt Niveau 1, identique pour tous :**

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

**Contraintes de passation :**
- Nouvelle conversation vierge à chaque modèle (pas d'historique)
- Pas de système prompt personnalisé — interface web standard ou API avec `temperature: 0` si possible
- Un seul message, pas de relance ni de correction
- Noter le **temps de génération** si tu utilises l'API

**Modèles à tester** (suggestion) :

| Modèle | Interface suggérée |
|---|---|
| GPT-4o | chat.openai.com ou API |
| o3 | chat.openai.com |
| Claude Sonnet 4.5 | claude.ai |
| Claude Opus 4 | claude.ai |
| Gemini 2.5 Pro | aistudio.google.com |
| Qwen3 72B | API / OpenWebUI |
| Mistral Large | api.mistral.ai |

***

### Étape 3 — Collecter les sorties

Pour chaque modèle, copier le code produit dans des fichiers organisés ainsi :
```
benchmark/
├── gpt-4o/
│   ├── gf-live-search.php
│   ├── assets/gf-live-search.js
│   └── assets/gf-live-search.css
├── claude-sonnet/
│   └── ...
├── gemini-2.5-pro/
│   └── ...
```

Si un modèle produit tout dans un seul bloc → découper manuellement dans les bons fichiers avant de passer au scoring.

***

### Étape 4 — Soumettre à l'agent scoreur

Pour chaque dossier modèle, envoyer à l'agent :

> *"Voici l'implémentation produite par [NOM DU MODÈLE]. Score-la selon la grille."*
> + les fichiers du dossier en pièce jointe

L'agent retourne un JSON :
```json
{
  "model": "gpt-4o",
  "scores": {
    "activation_sans_erreur": 15,
    "filtrage_dom": 18,
    "condition_chargement": 8,
    "no_results_row": 10,
    "raccourcis_clavier": 6,
    "update_count_badge": 7,
    "preload_pagination": 0,
    "diacritiques": 5,
    "i18n": 3,
    "qualite_php": 4
  },
  "total": 76,
  "notes": {
    "activation_sans_erreur": "Fonctionne, singleton correct",
    "preload_pagination": "Non implémenté, utilise AJAX à la place"
  }
}
```

***

### Étape 5 — Agréger et comparer

Demander à l'agent d'agréger tous les JSON en un tableau récapitulatif. Tu obtiens quelque chose comme :

| Critère | GPT-4o | Claude | Gemini | Qwen3 |
|---|---|---|---|---|
| Activation | 15/15 | 15/15 | 15/15 | 10/15 |
| Filtrage DOM | 18/20 | 20/20 | 16/20 | 12/20 |
| Condition chargement | 8/10 | 10/10 | 6/10 | 4/10 |
| ... | | | | |
| **Total** | **76** | **88** | **71** | **54** |

***

## Points de vigilance

**Fiabilité du scoring IA** — L'agent scoreur peut être indulgent sur les critères subjectifs (qualité PHP). Pour les critères fonctionnels clés (filtrage DOM, raccourcis, preload), idéalement tester réellement le plugin dans un WP de test. Mais pour un benchmark rapide, le scoring IA est suffisant.

**Biais de scoring** — Utilise le même agent et le même modèle (ex: Claude Opus ou GPT-4o) pour scorer **tous** les rendus. Si tu changes d'agent entre deux modèles, les scores ne sont plus comparables.

**Le critère `preload_pagination`** est le plus discriminant — c'est le seul qui nécessite une vraie connaissance de `fetch` + `DOMParser` + pagination WP_List_Table. La majorité des modèles l'omettront ou le feront mal.