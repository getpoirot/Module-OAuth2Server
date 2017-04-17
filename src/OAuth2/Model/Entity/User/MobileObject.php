<?php
namespace Module\OAuth2\Model\Entity\User;

use Poirot\Std\Struct\aValueObject;


class MobileObject
    extends aValueObject
{
    protected $countryCode;
    protected $number;


    /**
     * MobileObject constructor.
     *
     * @param $optionsResource
     */
    function __construct($optionsResource)
    {
        $this->with(static::parseWith($optionsResource));

    }


    /**
     * Get Country Code
     *
     * @return string
     */
    function getCountryCode()
    {
        return $this->countryCode;
    }

    function getNumber()
    {
        return $this->number;
    }

    function __toString()
    {
        return $this->getCountryCode().' '.$this->getNumber();
    }

    // ..

    /**
     * Build Object With Provided Options
     *
     * @param array $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @return array Remained Options (if not throw exception)
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = true)
    {
        if (array_values($options) === $options) {
            // Given Array is not associated array
            (!isset($options[0])) ?: $options['country_code'] = $options[0];
            (!isset($options[1])) ?: $options['number']       = $options[1];
        } else {
            if (isset($options['country']))
                $options['country_code'] = $options['country'];
        }

        if (!isset($options['country_code']))
            $options['country_code'] = '+98';

        if (! (isset($options['country_code']) && isset($options['number'])) )
            throw new \InvalidArgumentException(sprintf(
                '"country_code" or "number" is required.'
            ));


        if (isset($options['country_code']))
            $this->countryCode = $options['country_code'];

        if (isset($options['number']))
            // Remove Spaces and Trim 0 from Left
            $this->number = ltrim(preg_replace('/\s+/', '', $options['number']), '0');
    }

    /**
     * @inheritdoc
     *
     * Also Parse Mobile Number From String
     */
    static function parseWith($optionsResource, array $_ = null)
    {
        return parent::parseWith($optionsResource);
    }
}
