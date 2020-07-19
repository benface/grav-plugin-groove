<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Groove\GrooveAPI;

/**
 * Class GroovePlugin
 * @package Grav\Plugin
 */
class GroovePlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onFormProcessed' => ['onFormProcessed', 0]
        ]);
    }

    /**
     * Add the Groove form handler
     * @param Event $event
     */
    public function onFormProcessed(Event $event)
    {
        switch ($event['action']) {
            case 'groove':
                $this->handleCreateTicket($event);
        }
    }

    /**
     * @param Event $event
     * @throws \Exception
     */
    protected function handleCreateTicket(Event $event)
    {
        $form = $event['form'];
        $params = $event['params'];

        $twig = $this->grav['twig'];
        $vars = ['form' => $form];
        $config = $this->grav['config'];

        if (!isset($params['body'])) {
            throw new \Exception('Groove "body" not set');
        }
        $body = $twig->processString($params['body'], $vars);

        if (!isset($params['from'])) {
            throw new \Exception('Groove "from" not set');
        }
        $from = $params['from'];
        if (!is_array($from)) {
            $from = ['email' => $from];
        }
        foreach ($from as $from_key => $from_value) {
            $from[$from_key] = $twig->processString($from_value, $vars);
        }

        $to = $twig->processString(isset($params['to']) ? $params['to'] : ($config->get('plugins.groove.to') ? $config->get('plugins.groove.to') : ''), $vars);
        if (!$to) {
            throw new \Exception('Groove "to" not set');
        }

        $optional_params = [];

        $subject = $twig->processString(isset($params['subject']) ? $params['subject'] : ($config->get('plugins.groove.subject') ? $config->get('plugins.groove.subject') : ''), $vars);
        if ($subject) {
            $optional_params['subject'] = $subject;
        }

        $groove_api = $this->getAPIWrapper();
        $groove_api->createTicket($body, $from, $to, $optional_params);
    }

    /**
     * @return Groove
     * @throws \Exception
     */
    protected function getAPIWrapper()
    {
        require_once __DIR__ . '/classes/GrooveAPI.php';
        $api_token = $this->grav['config']->get('plugins.groove.api_token');
        $groove_api = new GrooveAPI($api_token);
        return $groove_api;
    }
}
