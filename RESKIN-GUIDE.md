# 🎨 RESKIN — Owner’s Guide

**RESKIN is the private design studio for your storefront.**
It lets you change colours, type, spacing, cards, content and shop details — then publish those choices to the live CSS when you are ready.

> **The one rule to remember**
> Editing only changes the **draft + preview**. Nothing goes live until you press **Enregistrer**.

---

## ⚡ Quick start

1. Open **RESKIN** from your owner area.
2. Pick one of the **8 starter styles** — including **Original** if you want the normal design back.
3. Tweak the design and text in the left panel.
4. Watch the result in the live preview on the right.
5. Press **Enregistrer** when you are happy.

That is the whole flow: **choose → adjust → preview → Enregistrer**.

> Already have a look you like? Use **Sauvegarde** to create a safety copy before making big changes.

---

## 🧭 The screen at a glance

| Area | What it is for |
|---|---|
| **Left sidebar** | Jump between page sections and open saved styles |
| **Centre panel** | The controls for the section you selected |
| **Right preview** | A live iframe of your storefront |
| **Top toolbar** | Status, undo/redo, backup, export, reset, apply and **Enregistrer** |

RESKIN is split into **Design** and **Contenu** inside each section:

- **Design** changes the CSS variables and body attributes.
- **Contenu** changes the words stored in the site content table.

Design and content are saved together by **Enregistrer**, but content is not overwritten when you apply a style preset.

---

## 🚦 The two buttons people ask about

### Enregistrer — “make it real”

**Enregistrer is the normal save + publish button.**

It does four things:

1. Saves the current design values.
2. Saves the current content fields.
3. Writes the complete stylesheet to:

   ```text
   assets/css/reskin-theme.css
   ```

4. Activates that stylesheet on the storefront.

After that, the shop uses the CSS file exactly as it was saved. It will **not change again** until you press **Enregistrer** again from RESKIN.

> **Use it when:** you finished an edit and want customers to see it.

### Sauvegarde — “make a safety copy”

**Sauvegarde is not a second save button. It creates a dated JSON backup.**

It stores a copy of everything RESKIN can change:

- the current design values,
- the page content,
- the shop identity and settings.

The file goes into:

```text
storage/reskin/snapshots/
```

It is a recovery file for the developer/owner. It does **not** restore anything by itself.

> **Important:** if you have unsaved changes, **Sauvegarde saves them first** (which also publishes the CSS), then writes the JSON copy. For normal publishing, use **Enregistrer**.

**Simple way to remember:**

| Button | Think of it as | Goes live? |
|---|---|---|
| **Enregistrer** | Publish my design | ✅ Yes |
| **Sauvegarde** | Put a copy in the safe | ⚠️ It saves pending edits first, so yes if you had unsaved changes |

**Recommended habit:** press **Enregistrer** after normal edits. Press **Sauvegarde** before a big redesign or after an important milestone.

---

## 🧰 Top toolbar, button by button

### Status pill

Shows one of three states:

- **Enregistré** — the draft matches the last save.
- **Non enregistré** — you have changes that are not published.
- **Enregistrement…** — a request is running.

### ↩️ Undo / ↪️ Redo

Undo and redo the whole form, including design values and content fields.

| Action | Shortcut |
|---|---|
| Undo | `Ctrl + Z` (or `Cmd + Z`) |
| Redo | `Ctrl + Shift + Z` or `Ctrl + Y` |

- Up to **60 states** are kept.
- The history lives in the page; reloading RESKIN starts a fresh history.
- While typing inside an input, the browser keeps its normal text undo. Use the toolbar buttons to undo the whole form.
- Saving or resetting starts a new history point.

### 💾 Sauvegarde

Creates the dated JSON safety copy explained above.

### ⋯ More actions menu

A compact menu with:

- **Exporter le CSS** — downloads the generated stylesheet. If you have unsaved changes, RESKIN saves them first so the download matches what you see.
- **Réinitialiser le design** — returns the design controls to the original shipped look. **Content and shop information stay intact.** If the theme is currently applied, the CSS file is rewritten with the original design.

### ✅ Enregistrer

Saves and publishes. See the section above.

### ⏻ Appliquer / Retirer les styles

Normally you do **not** need this after **Enregistrer**, because saving activates the theme automatically.

- **Appliquer les styles** writes the current theme CSS and turns it on.
- **Retirer les styles** turns the theme off. It does **not** delete your settings or saved styles. The shop returns to its original design until you apply again.

---

## 🌟 The 8 starter styles

The starter strip above the editor is there to save you from starting with a blank canvas.

| Style | Mood | Good for |
|---|---|---|
| **Original** | The normal shipped design | Starting over or restoring a safe baseline |
| **Terre & Lin** | Warm, natural, editorial | Soft fashion, handmade, timeless collections |
| **Minuit** | Modern, crisp, deep navy + copper | Premium everyday wear, clean catalogues |
| **Rosé Atelier** | Soft, feminine, rounded | Dresses, gifting, boutique collections |
| **Sable & Olive** | Calm, minimal, natural | Minimal labels, quiet luxury, everyday basics |
| **Bordeaux & Crème** | Rich, timeless, deep burgundy + ivory | Eveningwear, elevated classics, gifting |
| **Océan** | Fresh, bright, lagoon blue | Summer collections, light everyday wear |
| **Mono** | Minimal, graphic, black + white | Streetwear, sharp catalogues, modern basics |

Each starter fills **every design control** with a complete, coherent set of values:

- colours and backgrounds,
- fonts and text sizes,
- spacing and corner radius,
- announcement bar,
- hero slides,
- offer panel,
- category chips,
- product cards,
- “Why choose us” panel,
- footer,
- buttons and hover effects.

### 🌱 Original — the full reset card

**Original is the complete default design** in one click. It restores:

- font family,
- body text size,
- line height,
- heading size and weight,
- container width and section spacing,
- radius,
- every colour and background,
- hero, offer, cards, chips, footer and button settings.

It does **not** touch your page content, products, categories, logo or shop information.

**Original is a draft reset:** the controls and preview update, but the storefront stays as it is until you press **Enregistrer**.

> Prefer an immediate reset? **⋯ → Réinitialiser le design** resets right away. The **Original** card is the safer visual option because you can preview it first.

### What a starter does **not** do

- It does **not** change your text, product names or page content.
- It does **not** publish anything immediately.
- It does **not** delete your saved styles.

Click a starter → the controls and preview update → adjust anything you want → press **Enregistrer**.

> Selecting a starter is a **draft action**. The shop changes only after **Enregistrer**.

---

## 🗂️ The sections explained

### Général

The base of the whole site:

- primary colour,
- page background,
- text colour,
- accent colour,
- font family,
- body text size,
- line height,
- section heading size and weight,
- container width,
- space between sections,
- general corner radius.

Change these first; they affect every section.

### Bandeau d’annonce

The thin scrolling bar at the very top:

- background, text and border colours,
- height,
- one message per line,
- country label.

Maximum **8 messages**.

### Hero / slider

The large homepage slider:

- light-slide background,
- dark-slide background and second gradient tone,
- slide radius,
- title size,
- eyebrow, title and text colours for both light and dark slides,
- content for all three slides,
- one button label and one real link per slide.

The **Lien du bouton** dropdown only lists real pages and active categories. Products are deliberately left out to keep the list short. An older product link stays selectable and is marked **“Lien produit actuel (non listé)”**.

### Bandeau promo

The offer panel with the countdown:

- panel style: gradient, solid or outline,
- panel colour and border,
- radius,
- title colour and size,
- text colour,
- badge, title, text, button label and link,
- countdown label.

### Catégories

The collection shortcuts below the hero:

- chip shape: pill, soft or square,
- chip background, border, text and icon colours,
- row title and link label.

> The categories themselves are created and renamed in **Gestion → Catégories**, not in RESKIN.

### Grille produits

The product-card grid:

- desktop columns: 2, 3 or 4,
- gap between cards,
- card style: elevated, outlined or flat,
- card background and border,
- border width,
- card radius,
- shadow strength,
- hover animation on/off,
- image radius and aspect ratio,
- price colour,
- featured-section eyebrow, title and link label.

### Engagements

The “Why choose us” panel:

- section background,
- card background,
- divider lines,
- title and text colours,
- icon background and icon colour,
- panel radius,
- eyebrow, heading and lead text,
- four card titles and descriptions.

### Pied de page

The dark footer block:

- background,
- heading, text, link and accent colours,
- description,
- logo subtitle,
- three column titles,
- two pills,
- WhatsApp label,
- bottom note.

### Boutons

Every storefront button:

- radius,
- horizontal padding,
- dark button background,
- button text colour,
- hover colour,
- style: solid, outline or soft,
- hover effect: none, lift, scale or fade.

---

## 👁️ Live preview

The right-hand panel is a real storefront preview.

- **Desktop / tablet / mobile** buttons change the preview width.
- Clicking a section in the sidebar scrolls the preview to that part of the page.
- Design and content updates appear immediately in the preview.
- The preview is still a **draft** until **Enregistrer**.

The preview iframe is sandboxed and loads from your own site, so it stays faithful to the actual storefront CSS.

---

## 🎨 Design vs Contenu

| Badge | Meaning | Saved by |
|---|---|---|
| **Design** | Colours, sizes, spacing, shapes, effects | Theme values + CSS |
| **Contenu** | Headings, paragraphs, labels, messages | Site content table |

A text control always has a small **Contenu** badge. Everything else is design.

The **Enregistrer** button saves both. The **Sauvegarde** copy also includes both.

---

## 🧺 Saved styles vs starter styles

These are two different things:

| Feature | What it is | Publishes? |
|---|---|---|
| **Starter style** | Built-in preset like Terre & Lin | ❌ No — draft only |
| **Style enregistré** | Your own named library item in the sidebar | ❌ No — loads a draft |
| **Enregistrer** | Writes the CSS source and activates it | ✅ Yes |
| **Sauvegarde** | Dated JSON backup in `storage/reskin/snapshots` | ⚠️ Saves pending edits first |
| **Exporter le CSS** | Downloads the stylesheet | ✅ Saves pending edits first |
| **Appliquer / Retirer** | Turns the saved theme on or off | ✅ Yes, for apply/remove |

### Saving your own style

1. Set the design the way you want.
2. Type a name under **Styles enregistrés**.
3. Press **Ajouter**.
4. The page reloads and the style appears in the list.

Your named style remembers design values only; it does not replace page content.

### Loading a saved style

Click its name in the sidebar. The controls and preview update as a **draft**. Press **Enregistrer** to publish it.

---

## 💾 What “permanent” means

**Enregistrer** writes a real CSS source file:

```text
assets/css/reskin-theme.css
```

The storefront links to that file with a cache-busting version based on its modification time. It only changes when RESKIN writes it again — normally on **Enregistrer**.

So:

- Editing controls = preview only.
- **Enregistrer** = write the CSS file + activate it.
- Reloading the shop = the same saved CSS, not a new generated look.

The dynamic `/reskin/theme.css` route still exists as a fallback for a fresh installation where the static file has not been written yet. On a normal configured site, the static file is used.

> Do not edit `assets/css/reskin-theme.css` by hand unless your developer explicitly asks. The next RESKIN save will replace it.

---

## 🔐 Owner space vs client admin

RESKIN is the **owner space**:

- design,
- page content,
- shop identity,
- WhatsApp number,
- delivery fee,
- logo and social links,
- backup and export.

The **client admin** is separate. It handles products and categories only.

This separation is intentional: a client can manage the catalogue without changing branding or store identity.

---

## 🛟 Troubleshooting

### “My changes are not on the shop”

1. Check the status pill: it should say **Enregistré**, not **Non enregistré**.
2. Press **Enregistrer**.
3. Hard-refresh the storefront (`Ctrl + F5`).
4. Check the preview footer: **Styles appliqués sur la boutique**.

### “Enregistrer says the CSS file cannot be written”

The folder `assets/css/` is not writable by PHP. Fix its permissions on the server.

### “Undo does not work in a text field”

That is intentional: while typing, the browser uses its own text undo. Click outside the field, then use the toolbar undo button or `Ctrl + Z`.

### “I removed the styles”

Press **Appliquer les styles** to turn the saved theme back on. Nothing was deleted.

### “I reset the design”

**Réinitialiser le design** restores the original shipped design values. Content and shop information are kept. Use **Sauvegarde** first if you want a copy of the previous look.

### “I want the previous version back”

Restore the relevant file from `storage/reskin/snapshots/` with your developer. Snapshots are JSON backups, not automatic rollbacks in the dashboard.

---

## 🧠 Glossary

| Term | Meaning |
|---|---|
| **Draft** | The unsaved state of the controls and preview |
| **Publish** | Write and activate the real CSS file through **Enregistrer** |
| **Starter** | A built-in complete style suggestion |
| **Saved style** | Your own named design preset |
| **Snapshot** | A dated JSON safety copy of theme, content and shop settings |
| **Token** | A CSS variable such as `--primary` or `--radius` |
| **Applied** | The RESKIN theme is currently active on the storefront |

---

## ✨ The short version

- **Starter styles** = 8 fast starting points, draft only.
- **Original** = the full normal design, previewed as a draft until you save.
- **Design** = how it looks.
- **Contenu** = what it says.
- **Preview** = instant visual check.
- **Enregistrer** = save + publish the CSS.
- **Sauvegarde** = dated safety copy.
- **Appliquer / Retirer** = turn the saved theme on or off.
- **Reset** = original design, content preserved.
- **Export** = download the generated CSS.

**Choose a style. Make it yours. Press Enregistrer. Done.**
