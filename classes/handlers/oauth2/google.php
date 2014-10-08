<?php
/**
 * @package nxcSocialNetworks
 * @class   nxcSocialNetworksOAuth2Google
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    24 Sep 2012
 **/

class nxcSocialNetworksOAuth2Google extends nxcSocialNetworksOAuth2
{
	public static $tokenType = nxcSocialNetworksOAuth2Token::TYPE_GOOGLE;

	protected function __construct() {		
		parent::__construct();

		$redirectURL = '/nxc_social_network_token/get_access_token/google';
		eZURI::transformURI( $redirectURL, false, 'full' );		
		
		$this->connection = new Google_Client();				
		$this->connection->setClientId( $this->appSettings['key'] );
		$this->connection->setClientSecret( $this->appSettings['secret'] );
		$this->connection->setRedirectUri( $redirectURL );
		$this->connection->setApplicationName( 'eZ Publish' );
		$this->connection->setAccessType( 'offline' );
		$this->connection->setApprovalPrompt('force');
		
		$this->cacheClass = new Google_Cache_File( $this->connection );
		$this->connection->setCache( $this->cacheClass );

		$this->setState();
	}

	public function getPersistenceTokenScopes() {
		return array( 'https://www.googleapis.com/auth/plus.me', 'https://www.googleapis.com/auth/youtube' );
	}

	public function getAuthorizeURL( array $scopes = null, $redirectURL = null ) {
		$this->connection->setScopes( $scopes );

		if( $redirectURL !== null ) {
			eZURI::transformURI( $redirectURL, false, 'full' );
			$this->connection->setRedirectUri( $redirectURL );
		}

		return $this->connection->createAuthUrl();
	}

	public function getAccessToken( $redirectURL = null ) {
		$http = eZHTTPTool::instance();
		if( $http->hasGetVariable( 'code' ) === false ) {
			throw new Exception( 'Wrong request token. Refresh the page or try again later.' );
		}

		if( $redirectURL !== null ) {
			eZURI::transformURI( $redirectURL, false, 'full' );
			$this->connection->setRedirectUri( $redirectURL );
		}
		$this->connection->authenticate( $http->getVariable( 'code' ) );
		$accessToken = $this->connection->getAccessToken();
		return array(
			'token'  => $accessToken,
			'secret' => null
		);
	}

	public function setState() {
		$http = eZHTTPTool::instance();

		if( $http->hasGetVariable( 'state' ) ) {
			$this->connection->setState( base64_encode( $http->getVariable( 'state' ) ) );
		}
	}
}
?>
