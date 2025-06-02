# UniWallet – Application de gestion d’un portefeuille crypto mutualisé

## ✨ Objectif du projet

UniWallet est une application web **privée**, destinée à un **administrateur unique**, permettant de gérer un **portefeuille collectif en cryptomonnaies** avec plusieurs clients investisseurs. Elle permet d’enregistrer les dépôts, calculer automatiquement les bénéfices en fonction de la **performance du portefeuille**, gérer les **entrées/sorties à date fixe**, appliquer des pénalités le cas échéant, et générer des rapports mensuels **personnalisés**.

---

## 👤 Rôles et accès

### Utilisateur unique : administrateur

* Accès privé, sécurisé par mot de passe + 2FA OTP (Google Authenticator)
* Aucun autre utilisateur ou client ne se connecte à l’application
* Toutes les opérations sont gérées par l’administrateur depuis un tableau de bord

---

## 🧠 Philosophie du système

* **Un portefeuille commun** : tous les fonds sont regroupés sur un wallet crypto unique géré par l’administrateur
* **Performances mutualisées** : les bénéfices sont partagés équitablement entre clients selon leur **poids dans le portefeuille** et leur **date d’entrée**
* **Entrées / sorties uniquement en début ou fin de mois**, à saisir manuellement pour faciliter le suivi
* **Pénalité de retrait anticipé** : 10% sur le capital si sortie avant 6 mois
* **Partage des bénéfices** : 50% des plus-values sont prélevées par l’administrateur, le reste est réparti entre les clients
* **Mise à jour manuelle de la valeur du portefeuille** : saisie volontaire par l’administrateur à la date de son choix (une seule par date)
* **Synchronisation future envisagée avec CoinMarketCap** : pour automatiser la mise à jour de la valeur du portefeuille (non utilisée pour le moment)

---

## 📊 Fonctionnalités principales

### 🔐 Sécurité

* Authentification par mot de passe + 2FA (Google Authenticator)
* Protection CSRF/XSS sur tous les formulaires
* Journalisation des connexions (adresse IP, date, succès/échec)
* Validation des données côté client et serveur

---

### 🗁 Architecture prévisionnelle des fichiers

```
/
├── index.php               # Page de connexion
├── clients.php             # Liste et gestion des clients
├── client_ajouter.php      # Ajout d’un client
├── client_modifier.php     # Modification client
├── client_retrait.php      # Gestion des retraits
├── client_detail.php       # Détails complets du client
├── wallet.php              # Saisie des performances du portefeuille
├── dashboard.php           # Résumé global et tableau de bord
├── historique.php          # Journalisation des actions
├── exportimport.php        # Import/export CSV
├── header.php              # Menu + infos clés portefeuille
├── db.php                  # Connexion base de données
├── config.php              # Fichier de configuration
```

---

### Gestion des fichiers et des versions de fichier

* Ajouter un commentaire en début de chaque fichier PHP avec : sa date de création au format dd\:mm\:yy et de son heure au format hh\:mm\:ss
* Ajouter un commentaire en début de chaque fichier PHP lorsqu'il est modifié avec : sa date de modification au format dd\:mm\:yy et de son heure de modification au format hh\:mm\:ss
* Chaque fichier enfant dispose d'une fonction retour à la page parent

---

### 🧲 Système d’Unités de Part Virtuelles (UPV)

* Chaque dépôt client génère un nombre d’UPV basé sur la **valeur du portefeuille au moment de l’entrée**, selon un mécanisme de **tokenisation**.
* Le principe : chaque UPV représente une **part virtuelle** du portefeuille, dont la valeur est fixée au moment de l’entrée, sans dilution des UPV précédentes.
* Le calcul :

  * Exemple : si la valeur totale du portefeuille est de 10 000 \$ pour 1 000 UPV, la valeur unitaire d’une UPV est de 10 \$.
  * Si un client entre avec 1 000 \$, il recevra 100 UPV.
* Ce système garantit que chaque client détient une fraction exacte et équitable du portefeuille, relative à sa date d'entrée.
* Les UPV permettent de distribuer équitablement les bénéfices futurs en fonction du nombre de parts détenues.
* Lorsqu’un client sort, ses UPV sont supprimées définitivement.
* Le **total des UPV en circulation** doit correspondre à l’ensemble des parts actives dans le portefeuille.

#### 🔄 Fonctionnement évolutif et équitable :

* À chaque entrée, de **nouvelles UPV sont créées** selon la valeur actuelle du portefeuille, **sans affecter les UPV existantes**.
* Les nouveaux entrants n'ont pas droit aux performances passées. Ils commencent à participer aux bénéfices **à partir de leur date d'entrée**.
* En cas de sortie, les UPV du client sont détruites et retirées du total circulant, maintenant l’équilibre global.
* Ce modèle offre une **transparence maximale** et une **justesse économique**, même avec des entrées et sorties fréquentes et asynchrones.

---

### clients.php (gestion)

Fonctionnalités :

* Ajouter, modifier, retirer, supprimer un client
* Filtres (statut, email, contrat, date d'entrée)
* Calcul automatique :

  * Bénéfices en fonction de la performance globale
  * Pénalité si retrait < 6 mois
  * Répartition équitable après retrait de 50% des bénéfices globaux
* Informations affichées (colonnes principales) :

  * Statut (actif / retiré / sous pénalité)
  * Contrat ID (6 caractères max, généré automatiquement)
  * Nom ou pseudo
  * Email
  * Date d’entrée
  * Capital déposé (en USDT/USDC)
  * Performance portefeuille (%)
  * Montant total brut (capital + bénéfices)
  * Montant net après prélèvement (50%) et éventuelle pénalité
  * UPV attribuées
  * Date de sortie (si applicable)
  * Pénalité appliquée (oui/non)
* Possibilité d’ajouter plusieurs contrats pour une même adresse email

---

### wallet.php

* L’administrateur saisit la **valeur manuelle du portefeuille (\$)** à une date donnée
* L’application :

  * Met à jour la **performance (%)**
  * Recalcule l’équivalent en UPV à distribuer
  * Répartit les gains sur la base de la nouvelle valeur totale
* Les performances sont conservées dans une table historique
* Chaque ligne saisie doit être modifiable (toutes les valeurs sont modifiables) et supprimable.
* Données à afficher dans le tableau de la page, dans l'ordre :

  * Date (Date où la valeur du portefeuille a été entrée/actualisée)
  * Capital (Capital total déposé par tous les clients)
  * Bénéfices (Valeur totale des bénéfices réalisés)
  * Commission (Valeur de la commission sur les bénéfices de 50%)
  * Valeur totale ((Capital + bénéfices) - commission de 50%)
  * Actions (modifier/supprimer une ligne)

---

### dashboard.php

* Affiche :

  * Nombre total de clients actifs
  * Total des fonds déposés
  * Valeur actuelle du portefeuille
  * Performance cumulée (%)
  * Total UPV
  * Historique des dernières modifications
  * Rappels : retraits en attente, clients sortables (>6 mois)
  * Graphiques :

    * Courbe de l’évolution de la valeur du portefeuille
    * Histogramme des performances mensuelles
    * Répartition des UPV entre clients

---

### historique.php

* Enregistre toutes les actions sur les clients :

  * Ajout / modification / retrait
  * Pénalité appliquée
  * Montants avant/après
  * Auteur (admin unique)
* Tri, filtre, export CSV

---

### Génération de rapports

* Rapport mensuel par client :

  * Informations personnelles (Email, Nom ou Pseudo)
  * Numéro de contrat
  * Date d’entrée
  * Montant déposé
  * Bénéfices réalisés en %
  * Montant net (après prélèvement de la commission admin + pénalité éventuelle)
* Génération en HTML et PDF
* Envoi automatique par email (SMTP / PHPMailer)
* Chaque rapport comporte la date au moment où le rapport est généré. Les valeurs partagées dépendent de la date à laquelle le rapport a été généré.
* Un historique complet de tous les rapports mensuels est conservé. Les clients ne peuvent pas les consulter mais l’administrateur peut les renvoyer sur demande.

---

### Sauvegardes / Export CSV

* Export des données clients
* Export de l’historique
* Sauvegarde manuelle ou automatique de la base (SQL Dump)
* Sauvegarde déclenchée :

  * Soit à la demande de l’administrateur
  * Soit automatiquement à chaque mise à jour du portefeuille
* Synchronisation envisagée avec Dropbox :

  * Conserver les 5 dernières sauvegardes (FIFO)
  * La sauvegarde la plus ancienne est écrasée à chaque nouvelle entrée
* Import CSV de nouveaux clients possible avec modèle standardisé

---

## 💰 Modèle économique intégré

| Éléments                                  | Règles                                                          |
| ----------------------------------------- | --------------------------------------------------------------- |
| Dépôts clients                            | Libres, à date d’entrée fixée manuellement                      |
| Sorties                                   | Uniquement début ou fin de mois                                 |
| Durée minimale avant sortie sans pénalité | 6 mois                                                          |
| Pénalité avant 6 mois                     | 10% du capital initial                                          |
| Répartition bénéfices                     | 50% pour l’admin, 50% partagés entre clients au prorata des UPV |

---

## ⚖️ Stack technique

* PHP natif
* MySQL (PlanetHoster)
* Tailwind CSS
* PHPMailer (SMTP)
* Authentification : login + OTP 2FA
* PDF : DomPDF ou TCPDF
* Interface responsive

---

## 📊 Schéma SQL

Voici la structure des tables à créer dans MySQL :

### 1. `clients`

```sql
CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  contrat_id CHAR(6) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL,
  nom VARCHAR(255),
  date_entree DATE NOT NULL,
  capital_initial DECIMAL(18,2) NOT NULL,
  statut ENUM('actif', 'retire', 'penalite') DEFAULT 'actif',
  performance_percent DECIMAL(6,2) DEFAULT 0,
  montant_brut DECIMAL(18,2) DEFAULT 0,
  montant_net DECIMAL(18,2) DEFAULT 0,
  upv_attribuees DECIMAL(18,6) DEFAULT 0,
  date_sortie DATE DEFAULT NULL,
  penalite_appliquee BOOLEAN DEFAULT FALSE
);
```

### 2. `performances`

```sql
CREATE TABLE performances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_enregistrement DATE NOT NULL UNIQUE,
  capital_total DECIMAL(18,2) NOT NULL,
  benefices_totaux DECIMAL(18,2) NOT NULL,
  commission_admin DECIMAL(18,2) NOT NULL,
  valeur_portefeuille DECIMAL(18,2) NOT NULL
);
```

### 3. `upv`

```sql
CREATE TABLE upv (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_client INT NOT NULL,
  quantite DECIMAL(18,6) NOT NULL,
  date_creation DATE NOT NULL,
  FOREIGN KEY (id_client) REFERENCES clients(id) ON DELETE CASCADE
);
```

### 4. `retraits`

```sql
CREATE TABLE retraits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_client INT NOT NULL,
  date_retrait DATE NOT NULL,
  montant_recu DECIMAL(18,2) NOT NULL,
  penalite DECIMAL(18,2) DEFAULT 0,
  commission DECIMAL(18,2) DEFAULT 0,
  upv_retires DECIMAL(18,6) DEFAULT 0,
  FOREIGN KEY (id_client) REFERENCES clients(id) ON DELETE CASCADE
);
```

### 5. `journal`

```sql
CREATE TABLE journal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_action DATETIME NOT NULL,
  type_action VARCHAR(100) NOT NULL,
  auteur VARCHAR(100) DEFAULT 'admin',
  id_client INT,
  description TEXT,
  FOREIGN KEY (id_client) REFERENCES clients(id) ON DELETE SET NULL
);
```

### 6. `rapports`

```sql
CREATE TABLE rapports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_client INT NOT NULL,
  date_rapport DATE NOT NULL,
  lien_pdf VARCHAR(255),
  FOREIGN KEY (id_client) REFERENCES clients(id) ON DELETE CASCADE
);
```

### 7. `admin`

```sql
CREATE TABLE admin (
  id INT PRIMARY KEY,
  login VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  otp_secret VARCHAR(255) NOT NULL
);
```

---

## 📋 Confidentialité & mentions

* Projet privé dans un cadre souverain, entre individus libres
* Aucune licence publique
* Les données sont à usage exclusivement personnel
* La participation au portefeuille se réalise par contrat privé
