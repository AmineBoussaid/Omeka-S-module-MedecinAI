# 🩺 MedecinAI – Module Omeka S

Module Omeka S permettant de générer automatiquement des recommandations médicales à partir de transcriptions vocales, via une intégration directe avec **Ollama** (LLM local ou distant).

## 📦 Installation

1. Télécharger le module :

   * Depuis GitHub : [https://github.com/AmineBoussaid/Omeka-S-module-MedecinAI](https://github.com/AmineBoussaid/Omeka-S-module-MedecinAI)
   * Ou installer une Release versionnée si disponible.
2. Décompresser l’archive ZIP.
3. Renommer le dossier en **MedecinAI** *(important : le nom du dossier doit correspondre exactement au nom du module)*.
4. Copier le dossier dans :

   ```
   /modules/
   ```
5. Dans l’interface admin Omeka S, aller dans :
   **Modules → Installer MedecinAI**

## ⚙️ Configuration

### Activer l’intégration Ollama

Active la génération automatique des recommandations à partir de la transcription vocale.

### URL de l’API Ollama

Par défaut :

```
http://localhost:11434/api/generate
```

Modifier cette URL si Ollama tourne sur un autre serveur :

```
http://ADRESSE_SERVEUR:11434/api/generate
```

### Modèle Ollama

Nom du modèle de langage utilisé, par exemple :

* `gpt-oss:120b-cloud`
* `llama3.1`
* `mistral`
* `phi3`
  Le modèle doit être installé dans votre instance Ollama (`ollama list`).

### Langue de réponse

Langue dans laquelle le modèle doit générer les recommandations.
Valeur recommandée : **Français**

### Prompt système

Instruction transmise au modèle IA.
Utiliser `{transcription}` comme placeholder.

Prompt par défaut :

```
En tant que médecin, analysez les symptômes suivants et fournissez des recommandations médicales : {transcription}
```

Prompt recommandé (plus sécurisé) :

```
Tu es un assistant médical. Analyse les symptômes suivants et fournis des recommandations générales à visée informative, sans établir de diagnostic. Symptômes : {transcription}
```

### Température (créativité)

Contrôle la créativité de la réponse.

* 0.0 → réponse très prévisible
* 2.0 → réponse très créative

Valeur recommandée : 0.7
Pour éviter les hallucinations médicales : 0.5

### Nombre maximum de tokens

Longueur maximale de la réponse générée.
Valeur recommandée : 500

## 📝 Fonctionnement

1. Le module récupère automatiquement la transcription vocale liée à l’item.
2. Il envoie la transcription à Ollama via l’API.
3. Le modèle génère une recommandation médicale personnalisée.
4. La recommandation est stockée ou affichée dans l’interface Omeka S.

## 🛠️ Dépendances

* Omeka S (version compatible selon `module.ini`)
* Ollama installé en local ou sur un serveur distant → [https://ollama.com](https://ollama.com)

## 👤 Auteur

**Amine Boussaid**
GitHub : [https://github.com/AmineBoussaid](https://github.com/AmineBoussaid)
