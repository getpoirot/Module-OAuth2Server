<?php
namespace Module\OAuth2\Model\Entity\User;

use Module\OAuth2\Interfaces\Model\iUserGrantObject;
use Poirot\Std\Struct\DataOptionsOpen;


class GrantObject
    extends DataOptionsOpen
    implements iUserGrantObject
{
    protected $type;
    protected $value;
    protected $options = array();
    protected $is_validated = false;

    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    function setType($type)
    {
        $this->type = (string) $type;
        return $this;
    }

    /**
     * Get Type
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Set Value
     * @param mixed $value
     * @return $this
     */
    function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get Value
     * @return mixed
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Set Options
     * @param array $options
     * @return $this
     */
    function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * get Options
     * @return array
     */
    function getOptions()
    {
        return $this->options;
    }

    /**
     * Insert an Option into Options array
     * @param string $option
     * @param string $value
     * @return $this
     */
    function addOption($option, $value)
    {
        $this->options[$option] = $value;
        return $this;
    }
}