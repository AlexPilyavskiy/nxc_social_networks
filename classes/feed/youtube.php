<?php
/**
 * @package nxcSocialNetworks
 * @class   nxcSocialNetworksFeedYoutube
 * @author  Alex Pilyavskiy <spi@nxc.no>
 * @date    07 Oct 2014
 **/

class nxcSocialNetworksFeedYoutube extends nxcSocialNetworksFeed
{
	protected static $cacheDirectory     = 'nxc-google';
	protected static $debugMessagesGroup = 'NXC Social Networks Youtube feed';

	private $acessToken = null;

	public function __construct() {
		parent::__construct();

		$OAuth2 = nxcSocialNetworksOAuth2::getInstanceByType( 'google' );
		$token  = $OAuth2->getToken();
		$tmp    = json_decode( $token->attribute( 'token' ), true );
		try{
			$OAuth2->connection->refreshToken( $tmp['refresh_token'] );
			$this->API = new Google_Service_YouTube( $OAuth2->connection );
		} catch( Exception $e ) {
			eZDebug::writeError(
				$e->getMessage(),
				self::$debugMessagesGroup
			);
		}
	}

	public function getActivitiesList( $channel_id = false, $limit = 20 ) {
		$result = array( 'result' => array() );

		$accumulator = $this->debugAccumulatorGroup . '_youtube_activities_list';
		eZDebug::accumulatorStart(
			$accumulator,
			$this->debugAccumulatorGroup,
			'youtube/activities/list'
		);

		$cacheFileHandler = $this->getCacheFileHandler( '_youtube_activities_list', array( $channel_id, $limit ) );
		try{
			if( $this->isCacheExpired( $cacheFileHandler ) ) {
				eZDebug::writeDebug(
					array( 'channelId' => $channel_id, 'limit' => $limit ),
					self::$debugMessagesGroup
				);
				
				$requestParams = array( 'maxResults' => $limit );
				
				if ( $channel_id == 'mine' || !$channel_id ) {
					$requestParams['mine'] = true;
				}
				else if ( $channel_id ) {
					$requestParams['channelId'] = $channel_id;
				}

				$response = $this->API->activities->listActivities(
					'snippet,contentDetails', $requestParams
				);

				$activities  = array();
				$currentTime = time();
				foreach( $response->getItems() as $activity ) {							
					$activity = $activity->toSimpleObject();
					$createdAt = strtotime( $activity->snippet['publishedAt'] );

					$activity->created_ago       = self::getCreatedAgoString( $createdAt, $currentTime );
					$activity->created_timestamp = $createdAt;
					//$activity->snippet = $snippet;
					$activity = (array)$activity;
					
					//$activityArray = nxcSocialNetworksBase::objectToArray($activity);

					$activities[] = $activity;
				}

				$cacheFileHandler->fileStoreContents( $cacheFileHandler->filePath, serialize( $activities ) );
			} else {
				$activities = unserialize( $cacheFileHandler->fetchContents() );
			}

			eZDebug::accumulatorStop( $accumulator );
			$result['result'] = $activities;
			return $result;
		} catch( Exception $e ) {
			eZDebug::accumulatorStop( $accumulator );
			eZDebug::writeError( $e, self::$debugMessagesGroup );
			return $result;
		}
	}
	
	public function getVideosList( $playlist_id = false, $limit = 20 ) {
		$result = array( 'result' => array() );

		$accumulator = $this->debugAccumulatorGroup . '_youtube_videos_list';
		eZDebug::accumulatorStart(
			$accumulator,
			$this->debugAccumulatorGroup,
			'youtube/videos/list'
		);

		$cacheFileHandler = $this->getCacheFileHandler( '_youtube_videos_list', array( $playlist_id, $limit ) );
		try{
			if( $this->isCacheExpired( $cacheFileHandler ) ) {
				eZDebug::writeDebug(
					array( 'playlistId' => $playlist_id, 'limit' => $limit ),
					self::$debugMessagesGroup
				);
				
				$videos = array();
				$requestParams = array( 'maxResults' => $limit );
				
				if ( $playlist_id == 'mine' || !$playlist_id ) {
					$playlists = array();
					// get playlistId for current user
					$playlistRequest = $this->API->channels->listChannels( 'contentDetails', array('mine' => true));
					if ( $playlistRequest ) {
						foreach( $playlistRequest->getItems() as $playList ) {						
							$uploadsListId = $playList['contentDetails']['relatedPlaylists']['uploads'];
							$this->getVideosInPlayList( $videos, $uploadsListId, $limit );
						}						
					}
				}
				else if ( $playlist_id ) {					
					$this->getVideosInPlayList( $videos, $playlist_id, $limit );
				}											

				$cacheFileHandler->fileStoreContents( $cacheFileHandler->filePath, serialize( $videos ) );
			} else {
				$videos = unserialize( $cacheFileHandler->fetchContents() );
			}

			eZDebug::accumulatorStop( $accumulator );
			$result['result'] = $videos;
			return $result;
		} catch( Exception $e ) {
			eZDebug::accumulatorStop( $accumulator );
			eZDebug::writeError( $e, self::$debugMessagesGroup );
			return $result;
		}
	}
	
	private function getVideosInPlayList( &$videos, $playlistId, $limit ) {
		$currentTime = time();
		$requestParams = array( 'maxResults' => $limit,
			'playlistId' => $playlistId );
		$response = $this->API->playlistItems->listPlaylistItems(
			'snippet,contentDetails', $requestParams
		);
		foreach( $response->getItems() as $activity ) {							
			$activity = $activity->toSimpleObject();
			$createdAt = strtotime( $activity->snippet['publishedAt'] );

			$activity->created_ago       = self::getCreatedAgoString( $createdAt, $currentTime );
			$activity->created_timestamp = $createdAt;
			//$activity->snippet = $snippet;
			$activity = (array)$activity;

			//$activityArray = nxcSocialNetworksBase::objectToArray($activity);

			$videos[] = $activity;
		}		
	}
	
}


?>
