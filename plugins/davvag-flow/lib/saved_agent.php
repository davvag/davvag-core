<?php

class SavedAgentWorkflow {
    public static function run($input) {
        if (!defined("TENANT_RESOURCE_LOCATION")) {
            throw new Exception("The active tenant could not be resolved.");
        }

        $serviceFile = TENANT_RESOURCE_LOCATION . "/apps/ai-agent-creator/services/creator-api/service.php";
        if (!file_exists($serviceFile)) {
            throw new Exception("The ai-agent-creator runtime is not installed for this tenant.");
        }

        require_once($serviceFile);
        if (!class_exists("\\ai_agent_creator\\CreatorService")) {
            throw new Exception("The saved-agent runtime could not be loaded.");
        }

        $service = new \ai_agent_creator\CreatorService();
        $result = $service->runAgent($input);
        if (!$result || !isset($result->success) || !$result->success) {
            $message = $result && isset($result->message) ? $result->message : "The saved AI agent failed.";
            throw new Exception($message);
        }

        return $result;
    }
}

?>
