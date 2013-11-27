<?php

namespace Grid\FacebookLogin\Authentication\OAuth;

use Zork\Stdlib\String;
use Zork\Db\SiteInfo;
use Zork\Db\SiteInfoAwareTrait;
use Zork\Db\SiteInfoAwareInterface;
use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Http\PhpEnvironment\Response as PhpResponse;
use Zend\Session\ManagerInterface as SessionManagerInterface;
use Zork\Session\ContainerAwareTrait as SessionContainerAwareTrait;

/**
 * Client
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Client implements SiteInfoAwareInterface
{

    use SiteInfoAwareTrait,
        SessionContainerAwareTrait;

    /**
     * @const string
     */
    const DIALOG_URI = 'https://www.facebook.com/dialog/oauth';

    /**
     * @const string
     */
    const ACCESS_URI = 'https://graph.facebook.com/oauth/access_token';

    /**
     * @const string
     */
    const API_URI    = 'https://graph.facebook.com/me';

    /**
     * @var \Zend\Http\Client
     */
    protected $httpClient;

    /**
     * @return \Zend\Http\Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param \Zend\Http\Client $httpClient
     * @return \FacebookLogin\Authentication\OAuth\Client
     */
    public function setHttpClient( HttpClient $httpClient )
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Constructor
     *
     * @param \Zend\Http\Client $httpClient
     * @param string $returnUri
     */
    public function __construct( HttpClient                 $httpClient,
                                 SessionManagerInterface    $sessionManager,
                                 SiteInfo                   $siteInfo )
    {
        $this->setHttpClient( $httpClient )
             ->setSessionmanager( $sessionManager )
             ->setSiteInfo( $siteInfo );
    }

    /**
     * Login
     *
     * @param \Zend\Http\Request $request
     * @param \Zend\Http\Response $response
     * @return null|array|\Zend\Http\Response
     */
    public function login( array $options,
                           HttpRequest $request,
                           HttpResponse $response = null )
    {
        if ( null === $response )
        {
            $response = new PhpResponse;
        }

        $session    = $this->getSessionContainer();
        $code       = $request->getQuery( 'code' );

        if ( empty( $options['redirect_uri'] ) )
        {
            $options['redirect_uri'] = $request->getUri()
                                               ->getScheme()
                                     . '://'
                                     . $this->getSiteInfo()
                                            ->getFulldomain()
                                     . $request->getRequestUri();
        }

        if ( empty( $code ) )
        {
            $session['state']        = String::generateRandom( 32 );
            $session['redirect_uri'] = $options['redirect_uri'];

            $response->setContent( '' )
                     ->setStatusCode( 302 )
                     ->getHeaders()
                     ->clearHeaders()
                     ->addHeaderLine(
                         'Location',
                         static::DIALOG_URI . '?' .
                         http_build_query( array(
                             'client_id'    => $options['client_id'],
                             'redirect_uri' => $options['redirect_uri'],
                             'state'        => $session['state'],
                             'scope'        => 'email',
                         ) )
                     );

            if ( $response instanceof PhpResponse )
            {
                $response->send();
                exit();
            }
            else
            {
                return $response;
            }
        }

        $state = $request->getQuery( 'state' );

        if ( empty( $session['state'] ) || $state !== $session['state'] )
        {
            return null;
        }

        $client = $this->getHttpClient();

        $params = null;
        @ parse_str(
            $client->setMethod( 'GET' )
                   ->setUri( static::ACCESS_URI )
                   ->setParameterGet( array(
                       'client_id'      => $options['client_id'],
                       'redirect_uri'   => $session['redirect_uri'],
                       'client_secret'  => $options['client_secret'],
                       'code'           => $code,
                   ) )
                   ->send()
                   ->getBody(),
            $params
        );

        unset( $session['state'] );
        unset( $session['redirect_uri'] );

        if ( empty( $params['access_token'] ) )
        {
            return null;
        }

        return @ json_decode(
            $client->setMethod( 'GET' )
                   ->setUri( static::API_URI )
                   ->setParameterGet( array(
                       'access_token' => $params['access_token'],
                   ) )
                   ->send()
                   ->getBody(),
            true
        );
    }

}
