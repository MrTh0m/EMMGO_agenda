# 📚 Devoirs EMMGO — Dashboard

Suivi des devoirs et échéances emlyon business school.  
Un seul fichier HTML statique — hébergeable gratuitement sur GitHub Pages ou Netlify.

---

## 🚀 Mise en ligne en 3 minutes

### Option A — GitHub Pages (gratuit)

1. Crée un dépôt GitHub (public ou privé)
2. Upload `index.html` à la racine
3. Va dans **Settings → Pages → Branch: main → Save**
4. Ton dashboard est accessible sur `https://TON_PSEUDO.github.io/NOM_DU_REPO`

### Option B — Netlify (gratuit, encore plus simple)

1. Va sur [netlify.com](https://netlify.com) → **Add new site → Deploy manually**
2. Glisse-dépose le fichier `index.html`
3. C'est en ligne instantanément sur une URL Netlify

---

## ⚙️ Configuration

L'URL ICS Brightspace est pré-remplie dans le fichier.  
Pour la changer, modifie cette ligne dans `index.html` :

```
value="https://emlyon.brightspace.com/d2l/le/calendar/feed/user/feed.ics?token=TON_TOKEN"
```

---

## ⚠️ Limitation CORS

Le serveur Brightspace bloque les requêtes cross-origin depuis un navigateur.  
Le dashboard essaie automatiquement deux proxies CORS publics en cascade.  
Si les deux échouent (rare), un formulaire de secours permet de coller  
le contenu du fichier `.ics` téléchargé manuellement depuis Brightspace.

**Pour éviter entièrement ce problème** → utilise la version `serve.py`  
(le fichier `devoirs.html` + `serve.py`) qui télécharge le calendrier côté serveur.

---

## 🔒 Confidentialité

- Aucune donnée n'est envoyée à un serveur tiers  
- Le token ICS dans l'URL est personnel — ne partage pas le lien public du dashboard  
- Pour un usage privé, préfère un dépôt GitHub **privé** + Netlify avec mot de passe

---

## 📋 Fonctionnalités

- Détection automatique des devoirs (`Assessment`, `Co-construction`, `à échéance`)
- Compte à rebours coloré (rouge ≤3j, orange ≤7j, vert ≥15j)
- Filtres : Tous / Individuel / Collectif / Passés
- **Copier tâche** → presse-papiers → coller dans Google Tasks ou Microsoft To Do
- **Google Cal.** / **Outlook Cal.** → crée un événement à la date d'échéance
- Export `.ics` filtré (importable dans n'importe quel calendrier)
- Dark mode automatique
- Mémorisation de l'URL ICS (localStorage)
