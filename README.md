# 📚 EMMGO Dashboard — Devoirs & Live Sessions

Suivi des devoirs et live sessions emlyon business school.
Un seul fichier HTML statique + un proxy serveur — hébergeable sur ton propre serveur ou sur GitHub Pages / Netlify.

---

## 📁 Fichiers

| Fichier | Rôle |
|---|---|
| `index.html` | Dashboard principal (interface utilisateur) |
| `proxy.php` | Proxy PHP côté serveur — récupère le calendrier ICS sans CORS |
| `ics-proxy.py` | Alternative Python si PHP sans cURL/OpenSSL |
| `ics-proxy.service` | Fichier systemd pour lancer le proxy Python au démarrage |
| `test-proxy.php` | Script de diagnostic — à supprimer après utilisation |

---

## 🚀 Installation sur serveur auto-hébergé (recommandé)

### Prérequis PHP
Le proxy PHP nécessite l'extension cURL (et donc OpenSSL).
Vérifie la disponibilité avec `test-proxy.php`, puis installe si nécessaire :

```bash
sudo apt install php8.2-curl
sudo systemctl restart apache2   # ou nginx / php8.2-fpm selon ta config
```

### Déploiement
1. Copie `index.html` et `proxy.php` dans le dossier servi par ton serveur web
2. Visite `https://ton-domaine/test-proxy.php` pour vérifier que tout fonctionne
3. Supprime `test-proxy.php` du serveur (il contient ton token ICS)

---

## 🐍 Alternative : proxy Python (si PHP sans cURL/OpenSSL)

Si tu ne peux pas installer les extensions PHP, le proxy Python utilise
la bibliothèque standard Python (SSL intégré, aucune dépendance externe).

### Déploiement

```bash
# Adapter le chemin selon ton serveur
sudo cp ics-proxy.py /var/www/html/emmgo/
sudo cp ics-proxy.service /etc/systemd/system/

# Si besoin, édite le chemin ExecStart dans ics-proxy.service, puis :
sudo systemctl enable --now ics-proxy
```

Le proxy écoute sur `http://127.0.0.1:8766`.

### Configuration Apache

```apache
# Dans ton VirtualHost
ProxyPass        /proxy http://127.0.0.1:8766/
ProxyPassReverse /proxy http://127.0.0.1:8766/
```

### Configuration Nginx

```nginx
location /proxy {
    proxy_pass http://127.0.0.1:8766;
}
```

### Adapter index.html

Dans `index.html`, remplacer `proxy.php` par `proxy` dans la liste `CORS_PROXIES` :

```js
// Avant
u => ({ url: `proxy.php?url=${encodeURIComponent(u)}`, isJson: false }),

// Après
u => ({ url: `proxy?url=${encodeURIComponent(u)}`, isJson: false }),
```

---

## ☁️ Hébergement statique (GitHub Pages / Netlify)

Sans backend, le dashboard utilise des proxies CORS publics en cascade
(allorigins.win, corsproxy.io, codetabs.com). Ces proxies sont gratuits
mais parfois instables.

### GitHub Pages
1. Crée un dépôt GitHub (privé recommandé — le token ICS est personnel)
2. Upload `index.html` à la racine
3. **Settings → Pages → Branch: main → Save**
4. Accessible sur `https://pseudo.github.io/nom-du-repo`

### Netlify
1. [netlify.com](https://netlify.com) → *Add new site → Deploy manually*
2. Glisse-dépose `index.html`
3. En ligne instantanément

> 💡 Pour un accès privé sur Netlify : *Site settings → Identity → Enable*

---

## ⚙️ Configuration

L'URL ICS Brightspace est mémorisée dans le `localStorage` du navigateur
après le premier chargement. Pour la changer : bouton **Paramètres** dans le dashboard.

Pour modifier l'URL par défaut dans le code, chercher cette ligne dans `index.html` :

```
value="https://emlyon.brightspace.com/d2l/le/calendar/feed/user/feed.ics?token=TON_TOKEN"
```

---

## ✨ Fonctionnalités

- Détection automatique des devoirs (`Assessment`, `Co-construction`, `à échéance`)
- Détection des live sessions (`Cours distanciel`, `virtual-room.em-lyon.com`)
- Compte à rebours coloré : rouge ≤ 3j · orange ≤ 7j · vert ≥ 15j
- Fuseau horaire Europe/Paris correct (dates UTC du calendrier Brightspace)
- Filtres : type (individuel / collectif) + discipline avec compteurs
- Table de correspondance code → matière construite automatiquement depuis les sessions
- Boutons **Casier** (déposer le devoir) et **Afficher l'événement** (page Brightspace)
- Bouton **Rejoindre** pour les live sessions (salle virtuelle emlyon)
- **Copier tâche** → presse-papiers → coller dans Google Tasks ou Microsoft To Do
- **Google Cal.** / **Outlook** → crée un événement à la date d'échéance
- Export `.ics` filtré importable dans n'importe quel calendrier
- Dark mode automatique · mémorisation de l'URL ICS
