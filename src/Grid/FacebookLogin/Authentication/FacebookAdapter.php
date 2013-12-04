<?php

namespace Grid\FacebookLogin\Authentication;

use Zork\Stdlib\String;
use Zend\Authentication\Result;
use Zork\Model\ModelAwareTrait;
use Zork\Model\ModelAwareInterface;
use Zork\Session\ContainerAwareTrait;
use Zork\Model\Structure\StructureAbstract;
use Grid\User\Model\User\Structure as UserStructure;
use Zork\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zork\Factory\AdapterInterface as FactoryAdapterInterface;
use Zend\Authentication\Adapter\AdapterInterface as AuthAdapterInterface;

/**
 * AutoLoginAdapter
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class FacebookAdapter extends StructureAbstract
                   implements ModelAwareInterface,
                              AuthAdapterInterface,
                              FactoryAdapterInterface,
                              ServiceLocatorAwareInterface
{

    use ModelAwareTrait,
        ContainerAwareTrait,
        ServiceLocatorAwareTrait;

    /**
     * @const string
     */
    const OAUTH_TOKEN_ENDPOINT  = 'https://graph.facebook.com/oauth';

    /**
     * @const string
     */
    const OAUTH_API_ENDPOINT    = 'https://graph.facebook.com/me';

    /**
     * Return true if and only if $options accepted by this adapter
     * If returns float as likelyhood the max of these will be used as adapter
     *
     * @param  array $options;
     * @return float
     */
    public static function acceptsOptions( array $options )
    {
        return isset( $options['facebook'] );
    }

    /**
     * Return a new instance of the adapter by $options
     *
     * @param  array $options;
     * @return Grid\MultisitePlatform\Authentication\AutoLoginAdapter
     */
    public static function factory( array $options = null )
    {
        return new static( $options );
    }

    /**
     * Is registration enabled
     *
     * @return bool
     */
    protected function isRegistrationEnabled()
    {
        $config = $this->getServiceLocator()
                       ->get( 'Config'    )
                            [ 'modules'   ]
                            [ 'Grid\User' ];

        return ! empty( $config['features']['registrationEnabled'] );
    }

    /**
     * Get full callback url
     *
     * @return string
     */
    protected function getCallbackUrl()
    {
        /* @var $siteInfo \Zork\Db\SiteInfo */
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $service    = $this->getServiceLocator();
        $siteInfo   = $service->get( 'Zork\Db\SiteInfo' );
        $request    = $service->get( 'Request' );
        $uri        = $request->getUri();

        return $uri->getScheme() . '://'
             . $siteInfo->getFulldomain()
             . $request->getRequestUri();
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *         If authentication cannot be performed
     */
    public function authenticate()
    {
        $registered = false;
        $model      = $this->getModel();
        $settings   = $this->getServiceLocator()
                           ->get( 'Grid\Facebook\Model\ApplicationSettings\AdapterFactory' )
                           ->factory( array( 'application' => 'login' ) );

        $appId      = $settings->getSetting( 'appId' );
        $appSecret  = $settings->getSetting( 'appSecret' );

        if ( empty( $appId ) || empty( $appSecret ) )
        {
            return new Result(
                Result::FAILURE_UNCATEGORIZED,
                null,
                array(
                    'appId and/or appSecret not set',
                )
            );
        }

        $service = $this->getServiceLocator();
        $client  = new OAuth\Client(
            $service->get( 'Zend\Http\Client' ),
            $this->getSessionManager(),
            $service->get( 'Zork\Db\SiteInfo' )
        );

        $data = $client->login(
            array(
                'client_id'     => $appId,
                'client_secret' => $appSecret,
            ),
            $service->get( 'Request' ),
            $service->get( 'Response' )
        );

        if ( empty( $data ) || empty( $data['email'] ) )
        {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null,
                array(
                    'Cannot parse graph response or email not sent',
                )
            );
        }

        $email  = $data['email'];
        $user   = $model->findByEmail( $email );

        if ( empty( $user ) )
        {
            if ( ! $this->isRegistrationEnabled() )
            {
                return new Result(
                    Result::FAILURE_IDENTITY_NOT_FOUND,
                    null
                );
            }

            $displayName = empty( $data['name'] )
                ? preg_replace( '/@.*$/', '', $email )
                : $data['name'];

            $i = 1;
            $displayName    = UserStructure::trimDisplayName( $displayName );
            $originalName   = $displayName;

            while ( ! $model->isDisplayNameAvailable( $displayName ) )
            {
                $displayName = $originalName . ' ' . ++$i;
            }

            $user = $model->create( array(
                'confirmed'     => true,
                'status'        => 'active',
                'displayName'   => $displayName,
                'email'         => $email,
                'locale'        => ! empty( $data['language'] )
                                   ? $data['language']
                                   : (string) $this->getServiceLocator()
                                                   ->get( 'Locale' ),
                'password'      => String::generateRandom( 10 ),
            ) );

            if ( $user->save() )
            {
                $registered = true;
                $user       = $model->findByEmail( $email );
            }
            else
            {
                return new Result(
                    Result::FAILURE_UNCATEGORIZED,
                    null
                );
            }
        }

        if ( empty( $user ) || empty( $user->id ) || $user->isBanned() )
        {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null
            );
        }
        else if ( $user->isInactive() )
        {
            $user->makeActive();

            if ( ! $user->save() )
            {
                return new Result(
                    Result::FAILURE_UNCATEGORIZED,
                    null
                );
            }
        }

        $model->associateIdentity(
            $user->id,
            empty( $data['link'] )
                ? 'urn:facebook:' . (
                    empty( $data['id'] )
                        ? $email
                        : $data['id']
                )
                : $data['link']
        );

        return new Result(
            Result::SUCCESS,
            $user,
            array(
                'loginWith'     => 'facebook',
                'registered'    => $registered,
            )
        );
    }

}
