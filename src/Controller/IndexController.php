<?php
namespace MedecinAI\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Http\Client;
use Laminas\Http\Request;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        // Augmenter le temps d'exécution PHP à 300 secondes (5 minutes)
        set_time_limit(300);
        
        $settings = $this->settings();
        
        // Récupérer les configurations
        $ollamaEnabled = $settings->get('healthvoice_ollama_enabled', false);
        $ollamaApiUrl = $settings->get('healthvoice_ollama_api_url', 'http://localhost:11434/api/generate');
        $ollamaModel = $settings->get('healthvoice_ollama_model', 'llama3');
        $ollamaLanguage = $settings->get('healthvoice_ollama_language', 'fr');
        $ollamaPrompt = $settings->get('healthvoice_ollama_prompt', 'En tant que médecin, analysez les symptômes suivants et fournissez des recommandations médicales : {transcription}');
        $ollamaTemperature = $settings->get('healthvoice_ollama_temperature', '0.7');
        $ollamaMaxTokens = $settings->get('healthvoice_ollama_max_tokens', '500');
        
        $recommendation = null;
        $error = null;
        
        // Si le formulaire est soumis
        if ($this->getRequest()->isPost()) {
            $transcription = $this->params()->fromPost('transcription', '');
            
            if (!empty($transcription) && $ollamaEnabled) {
                try {
                    // Préparer le prompt avec la transcription
                    $finalPrompt = str_replace('{transcription}', $transcription, $ollamaPrompt);
                    
                    // Ajouter l'instruction de langue
                    $languageNames = [
                        'fr' => 'français',
                        'en' => 'English',
                        'es' => 'español',
                        'de' => 'Deutsch',
                        'it' => 'italiano',
                        'pt' => 'português',
                        'ar' => 'العربية',
                        'zh' => '中文'
                    ];
                    $languageName = $languageNames[$ollamaLanguage] ?? 'français';
                    $finalPrompt .= "\n\nRépondez uniquement en " . $languageName . ".";
                    
                    // Appeler l'API Ollama
                    $recommendation = $this->callOllamaApi(
                        $ollamaApiUrl,
                        $ollamaModel,
                        $finalPrompt,
                        floatval($ollamaTemperature),
                        intval($ollamaMaxTokens)
                    );
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            } elseif (!$ollamaEnabled) {
                $error = "L'intégration Ollama n'est pas activée. Veuillez l'activer dans les paramètres du module.";
            }
        }
        
        // Charger toutes les observations pour le select
        $api = $this->api();
        $observations = [];
        try {
            $templateId = $this->getObservationTemplateId();
            if ($templateId) {
                $response = $api->search('items', ['resource_template_id' => $templateId, 'limit' => 100]);
                $observations = $response->getContent();
            }
        } catch (\Exception $e) {
            // Silently fail if observations cannot be loaded
            error_log('MedecinAI: Erreur chargement observations: ' . $e->getMessage());
        }
        
        return new ViewModel([
            'ollamaEnabled' => $ollamaEnabled,
            'ollamaModel' => $ollamaModel,
            'ollamaLanguage' => $ollamaLanguage,
            'observations' => $observations,
            'recommendation' => $recommendation,
            'error' => $error,
        ]);
    }
    
    private function callOllamaApi($apiUrl, $model, $prompt, $temperature, $maxTokens)
    {
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => $temperature,
                'num_predict' => $maxTokens
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout long (5 minutes)
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Erreur cURL: " . $error);
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (!isset($result['response'])) {
            throw new \Exception("Réponse Ollama invalide : " . $response);
        }

        return $result['response'];
    }


    
    private function getObservationTemplateId()
    {
        $api = $this->api();
        try {
            $templates = $api->search('resource_templates', ['label' => 'Observation'])->getContent();
            if (!empty($templates)) {
                return $templates[0]->id();
            }
        } catch (\Exception $e) {
            // Return null if not found
        }
        return null;
    }
}
