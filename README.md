**Title:** Show HN: I benchmarked 14 LLMs on a WP plugin task after Copilot removed Claude Opus. Exactly zero used the native UI.

**The Context**
Recently, GitHub Copilot silently dropped support for Claude Opus on Pro accounts. Since Opus was my go-to model for my specific daily workflow—developing WordPress and Gravity Forms plugins—I was left looking for a reliable replacement. I decided to run a rigorous, blind benchmark across 14 state-of-the-art and local LLMs to objectively measure which model understands WordPress development best. To ensure a perfectly fair test, I always started with a completely fresh IDE and zero context for every single generation.

**The Prompt (Level 1)**
For this initial benchmark, I used a minimal "Level 1" prompt to see what the models would generate by default without heavy hand-holding. Here is the exact prompt I used (translated to English):

> Create a WordPress plugin named **GF Live Search** that adds real-time search to the Gravity Forms list page (`/wp-admin/?page=gf_edit_forms`).
> 
> The plugin must:
> - Instantly filter table rows without reloading the page
> - Only load its assets on the relevant page
> - Follow WordPress best practices (hooks, security, i18n)
> 
> Expected structure:
> gf-live-search/
> ├── gf-live-search.php
> └── assets/
>     ├── gf-live-search.js
>     └── gf-live-search.css
> └── languages/
>     ├── gf-live-search.pot

**The Evaluation Process & Prompt**
To establish a baseline, each generated plugin was compared against my own reference implementation (available here: [https://github.com/guilamu/gf-live-search](https://github.com/guilamu/gf-live-search)), which, while probably very perfectible itself, outlines the exact functional behavior I expected.

To avoid personal bias in the final scoring, I had **Gemini 3.1 Pro** act as the judge. I fed the generated code to Gemini using strictly anonymized folders (named `1`, `2`, `3`, etc.). Here is the exact evaluation prompt I gave to Gemini 3.1 Pro to grade them (translated from French to English):

> You are a code evaluator specializing in WordPress. You receive plugin implementations to evaluate.
>
> ## Reference Plugin
> The implementation for model X is available in directory /X.
> The reference implementation that the tested model had to recreate from scratch, starting from a minimal prompt, is available in the /corrigé référence directory.
>
> ## Evaluation Rules
> - Score on product behavior, NOT on code style or variable names.
> - A naming discrepancy (e.g., `noResultsRow` instead of `noResults`) is never an error.
> - A partially implemented feature receives partial points, not 0.
> - If a feature is absent, the criterion score is 0.
> - Do not rely on your knowledge of what the plugin "should" do: rely solely on the behavior described in the grid below.
> 
> ## Scoring Grid (100 pts)
> 
> **1. Activation without fatal error (15 pts)**
> The main PHP file is syntactically valid, defines the `ABSPATH` guard, and the plugin could activate without a fatal error. Check for: `if ( ! defined( 'ABSPATH' ) ) exit;`, defined constants, instantiated class.
> 
> **2. Functional DOM Filtering (20 pts)**
> The JS intercepts the native GF input (`#form_list_search` + `input[name="s"]` or equivalent), filters the `<tr>` rows of `tbody#the-list` by hiding/showing them without a page reload, and uses a debounce (any delay between 100ms and 300ms is valid).
> - 20 pts: filtering + debounce + row search text caching
> - 14 pts: filtering + debounce, no cache
> - 8 pts: functional filtering but no debounce
> - 0 pt: server-side AJAX, or jQuery form submit, or missing filter
> 
> **3. Strict loading condition (10 pts)**
> Assets are only loaded on the GF list page. Verify on the PHP side that the page is `gf_edit_forms` AND that we are not on a specific form editor (absence of `$_GET['id'] > 0`).
> - 10 pts: double condition (page + absence of form id)
> - 6 pts: condition on `gf_edit_forms` only
> - 0 pt: loading on all admin pages or missing condition
> 
> **4. "No results" row (10 pts)**
> A "no results" type row is injected into the DOM and displayed only when the active filter does not match any row. It must disappear when the field is cleared.
> - 10 pts: complete behavior (appears / disappears / translated or static text)
> - 5 pts: present but does not disappear correctly
> - 0 pt: absent
> 
> **5. Keyboard shortcuts (10 pts)**
> A keyboard shortcut focuses the search input. Ignore if the focus is already in an editable field (input, textarea, select, contenteditable).
> - 10 pts: Ctrl/Cmd+F AND the `/` key
> - 6 pts: only one of the two
> - 3 pts: shortcut present but without guard on editable fields
> - 0 pt: absent
> 
> **6. Counter update (10 pts)**
> The native GF counter (`.displaying-num`) is updated in real time to reflect the number of visible results. It is restored to its original value when the field is cleared.
> - 10 pts: update + restoration
> - 6 pts: update without restoration
> - 0 pt: absent
> 
> **7. Preloading paginated pages (10 pts)**
> If the GF list is paginated (multiple pages), the JS loads the other pages in the background via `fetch` so that the filter operates on all forms, not just the current page.
> - 10 pts: `fetch` + HTML parsing + injection of rows into the DOM
> - 4 pts: pagination mechanism present but incomplete or using WP AJAX
> - 0 pt: absent (filter limited to the current page only)
> 
> **8. Diacritics and case (5 pts)**
> The comparison is case-insensitive AND diacritic-insensitive (`é` finds `e`, `É` finds `é`).
> - 5 pts: `.toLowerCase()` + `.normalize('NFD')` + removal of accents
> - 2 pts: case-insensitive only
> - 0 pt: raw comparison
> 
> **9. Internationalization (5 pts)**
> User-visible strings are translatable.
> - 5 pts: `__()` / `_n()` on PHP side + mechanism to pass translations to JS (`wp_add_inline_script` or `wp_localize_script`)
> - 2 pts: `__()` on PHP side only, JS hardcoded
> - 0 pt: no i18n
> 
> **10. PHP code quality (5 pts)**
> - 5 pts: singleton class, `defined('ABSPATH')`, plugin constants (DIR/URL/VERSION), `admin_enqueue_scripts` hook (not `wp_enqueue_scripts`)
> - 3 pts: 3 out of 4 elements present
> - 1 pt: functional procedural code but missing standard WP patterns
> - 0 pt: invalid or unusable code
> 
> ## Expected Response Format
> Return ONLY a valid JSON block, without text before or after...

### The Findings

**1. The "Blind Spot": Re-inventing the wheel**
Out of 14 models, exactly **0** successfully hooked into the native Gravity Forms search input (`#form_list_search`). Instead of analyzing the DOM and integrating with the existing UI, every single model injected a brand new, redundant `<input>` into the page (via `document.createElement`, jQuery, or PHP hooks). 

**2. Complete lack of advanced UX foresight**
Because it wasn't explicitly asked for in the initial Level 1 prompt, no model anticipated the need for keyboard shortcuts, nor did any attempt to update the native item counter as rows were hidden. Zero models implemented background-fetching (`fetch()`) for paginated pages to make the search global.

**3. The Diacritics Separator**
Most models used a simple `.toLowerCase()` for filtering, which breaks on accents. Only a select few (Claude 4.7 Opus, Mimo v2.5 pro) implemented robust normalization (`.normalize('NFD').replace(/[\u0300-\u036f]/g, '')`) to handle case and diacritics correctly.

**4. Local models struggled (especially Gemma)**
The local inferences failed to keep up with cloud providers. Gemma4-26b underperformed significantly, generating a fatal PHP error (calling an undefined method) and scoring 18/100. The smaller Gemma4-e4b (32/100) provided a functional but naive JS implementation with zero translations or advanced features.

**5. Claude 4.7 Opus takes the top spot**
Despite failing the native UI integration like the others, **Claude 4.7 Opus** (using a planning prompt approach) scored the highest (68/100). It wrote performant JavaScript by pre-caching DOM text in data attributes, debouncing inputs (120ms), handling diacritics properly, and utilizing modern WordPress i18n (`wp_set_script_translations`). It stands out as the most capable direct replacement for Copilot Pro Opus.

### Price vs. Performance Observation: GLM 5.1
While Claude 4.7 Opus achieved the highest score, **GLM 5.1** secured a notable 2nd place (61/100). When comparing the OpenRouter pricing for these top-performing models, GLM 5.1 offers a highly competitive price-to-performance ratio:

- **GLM 5.1** (Score: 61): **$1.05** / 1M input | **$3.50** / 1M output
- **Claude Sonnet 4.6** (Score: 55): **$3.00** / 1M input | **$15.00** / 1M output *(~3-4x more expensive)*
- **Claude Opus 4.7** (Score: 68): **$5.00** / 1M input | **$25.00** / 1M output *(~5-7x more expensive)*

Delivering solid architecture (Singleton pattern, clean i18n, structured PHP) at this price point makes GLM 5.1 a very cost-effective alternative for daily automated coding tasks.

### The Leaderboard

1. **Claude 4.7 Opus plan** – 68
2. **GLM 5.1** – 61
3. **Claude 4.6 Opus plan** – 59
4. **Mimo v2.5 pro** – 58
5. **Qwen 3.6+** – 55
5. **Sonnet 4.6** – 55
7. **Gemini 3.1 pro** – 53
8. **Kimi K2.6** – 49
8. **GPT 5.4 xHigh** – 49
10. **Gemini 3 flash** – 47
11. **Claude 4.7 Opus fast** – 46
12. **Minimax m2.7** – 36
13. **Gemma4-e4b** *(Local rx6700)* – 32
14. **Gemma4-26b** *(Local CPU)* – 18

### Conclusion
When given a basic prompt, even the best LLMs default to the path of least resistance: "just make it work." Rather than attempting to analyze the implicit context (the existing DOM structure), they forcefully inject new elements. If you want native-feeling, fully integrated UX, you cannot rely on the model's implicit knowledge; you have to explicitly prompt for it. 

*(I will be testing Level 2 and Level 3 prompts next, feeding the models the actual HTML DOM structure to see if they adapt).*
