<?php
namespace Antavo\LoyaltyApps\Controller;

/**
 * @method _forward($action, $controller = null, $module = null, array $params = null)
 */
trait ControllerTrait
{
    /**
     * Sends a default 404 page to the output.
     */
    public function displayNotFound()
    {
        $this->_forward('defaultNoRoute');
    }
}
