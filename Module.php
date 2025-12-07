<?php
namespace MedecinAI;

use Omeka\Module\AbstractModule;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        
        // Paramètres Ollama
        $ollamaEnabled = $settings->get('healthvoice_ollama_enabled', false);
        $ollamaApiUrl = $settings->get('healthvoice_ollama_api_url', 'http://localhost:11434/api/generate');
        $ollamaModel = $settings->get('healthvoice_ollama_model', 'llama3');
        $ollamaLanguage = $settings->get('healthvoice_ollama_language', 'fr');
        $ollamaPrompt = $settings->get('healthvoice_ollama_prompt', 'En tant que médecin, analysez les symptômes suivants et fournissez des recommandations médicales : {transcription}');
        $ollamaTemperature = $settings->get('healthvoice_ollama_temperature', '0.7');
        $ollamaMaxTokens = $settings->get('healthvoice_ollama_max_tokens', '500');

        return '
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_enabled">Activer l\'intégration Ollama</label>
            </div>
            <div class="field-inputs">
                <input type="checkbox" name="healthvoice_ollama_enabled" id="healthvoice_ollama_enabled" value="1" ' . ($ollamaEnabled ? 'checked' : '') . ' />
                <p class="explanation">Permet de générer automatiquement des recommandations médicales à partir des transcriptions vocales.</p>
            </div>
        </div>
        
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_api_url">URL de l\'API Ollama</label>
            </div>
            <div class="field-inputs">
                <input type="text" name="healthvoice_ollama_api_url" id="healthvoice_ollama_api_url" value="' . htmlspecialchars($ollamaApiUrl, ENT_QUOTES) . '" class="form-control" style="width: 100%;" />
                <p class="explanation">URL complète de l\'endpoint Ollama (exemple: http://localhost:11434/api/generate)</p>
            </div>
        </div>
        
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_model">Modèle Ollama</label>
            </div>
            <div class="field-inputs">
                <input type="text" name="healthvoice_ollama_model" id="healthvoice_ollama_model" value="' . htmlspecialchars($ollamaModel, ENT_QUOTES) . '" class="form-control" style="width: 100%;" list="ollama_model_list" />
                <datalist id="ollama_model_list">
                    <option value="llama2">
                    <option value="llama3">
                    <option value="mistral">
                    <option value="mixtral">
                    <option value="codellama">
                    <option value="neural-chat">
                    <option value="phi">
                    <option value="orca-mini">
                    <option value="gpt-oss:120b-cloud">
                </datalist>
                <p class="explanation">Écrivez le modèle de langage à utiliser pour générer les recommandations. Vous pouvez choisir dans la liste ou écrire votre propre modèle.</p>
            </div>
        </div>
        
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_language">Langue de réponse</label>
            </div>
            <div class="field-inputs">
                <select name="healthvoice_ollama_language" id="healthvoice_ollama_language" class="form-control">
                    <option value="fr" ' . ($ollamaLanguage === 'fr' ? 'selected' : '') . '>Français</option>
                    <option value="en" ' . ($ollamaLanguage === 'en' ? 'selected' : '') . '>English</option>
                    <option value="es" ' . ($ollamaLanguage === 'es' ? 'selected' : '') . '>Español</option>
                    <option value="de" ' . ($ollamaLanguage === 'de' ? 'selected' : '') . '>Deutsch</option>
                    <option value="it" ' . ($ollamaLanguage === 'it' ? 'selected' : '') . '>Italiano</option>
                    <option value="pt" ' . ($ollamaLanguage === 'pt' ? 'selected' : '') . '>Português</option>
                    <option value="ar" ' . ($ollamaLanguage === 'ar' ? 'selected' : '') . '>العربية</option>
                    <option value="zh" ' . ($ollamaLanguage === 'zh' ? 'selected' : '') . '>中文</option>
                </select>
                <p class="explanation">Langue dans laquelle Ollama générera les recommandations médicales.</p>
            </div>
        </div>
        
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_prompt">Prompt système</label>
            </div>
            <div class="field-inputs">
                <textarea name="healthvoice_ollama_prompt" id="healthvoice_ollama_prompt" rows="5" class="form-control" style="width: 100%;">' . htmlspecialchars($ollamaPrompt, ENT_QUOTES) . '</textarea>
                <p class="explanation">Instruction donnée au modèle IA. Utilisez {transcription} comme placeholder pour insérer la transcription vocale du patient.</p>
            </div>
        </div>
        
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_temperature">Température (créativité)</label>
            </div>
            <div class="field-inputs">
                <input type="number" name="healthvoice_ollama_temperature" id="healthvoice_ollama_temperature" value="' . htmlspecialchars($ollamaTemperature, ENT_QUOTES) . '" step="0.1" min="0" max="2" class="form-control" style="width: 150px;" />
                <p class="explanation">Contrôle la créativité des réponses (0.0 = très prévisible, 2.0 = très créatif). Recommandé: 0.7</p>
            </div>
        </div>
        
        <div class="field">
            <div class="field-meta">
                <label for="healthvoice_ollama_max_tokens">Nombre maximum de tokens</label>
            </div>
            <div class="field-inputs">
                <input type="number" name="healthvoice_ollama_max_tokens" id="healthvoice_ollama_max_tokens" value="' . htmlspecialchars($ollamaMaxTokens, ENT_QUOTES) . '" min="100" max="2000" class="form-control" style="width: 150px;" />
                <p class="explanation">Limite la longueur de la réponse générée. Recommandé: 500</p>
            </div>
        </div>';
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        
        $params = $controller->getRequest()->getPost();
        
        // Sauvegarder les paramètres Ollama
        $settings->set('healthvoice_ollama_enabled', isset($params['healthvoice_ollama_enabled']) ? true : false);
        $settings->set('healthvoice_ollama_api_url', $params['healthvoice_ollama_api_url']);
        $settings->set('healthvoice_ollama_model', $params['healthvoice_ollama_model']);
        $settings->set('healthvoice_ollama_language', $params['healthvoice_ollama_language']);
        $settings->set('healthvoice_ollama_prompt', $params['healthvoice_ollama_prompt']);
        $settings->set('healthvoice_ollama_temperature', $params['healthvoice_ollama_temperature']);
        $settings->set('healthvoice_ollama_max_tokens', $params['healthvoice_ollama_max_tokens']);

        return true;
    }
}
