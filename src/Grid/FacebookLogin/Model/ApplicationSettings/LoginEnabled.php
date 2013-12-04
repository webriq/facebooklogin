<?php

namespace Grid\FacebookLogin\Model\ApplicationSettings;

use Grid\Facebook\Model\ApplicationSettings\AdapterFactory;

/**
 * LoginEnabled
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class LoginEnabled
{

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var LoginAdapter
     */
    protected $loginAdapter;

    /**
     * @param   AdapterFactory  $adapterFactory
     */
    public function __construct( AdapterFactory $adapterFactory )
    {
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @return  LoginAdapter
     */
    protected function getLoginAdapter()
    {
        if ( null === $this->loginAdapter )
        {
            $this->loginAdapter = $this->adapterFactory->factory( array(
                'application' => 'login',
            ) );
        }

        return $this->loginAdapter;
    }

    /**
     * @return  bool
     */
    public function __invoke()
    {
        $loginAdapter = $this->getLoginAdapter();

        return $loginAdapter->getSetting( 'enabled' , false )
            && $loginAdapter->hasSetting( 'appId' )
            && $loginAdapter->hasSetting( 'appSecret' );
    }

}
