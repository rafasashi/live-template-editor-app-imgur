<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Integrator_Imgur extends LTPLE_Client_Integrator {

	public function init_app() {
		
		if( isset($this->parameters['key']) ){
			
			$imgur_consumer_key 		= array_search('imgur_consumer_key', $this->parameters['key']);
			$imgur_consumer_secret 		= array_search('imgur_consumer_secret', $this->parameters['key']);
			$imgur_oauth_callback 		= $this->parent->urls->apps;

			if( !empty($this->parameters['value'][$imgur_consumer_key]) && !empty($this->parameters['value'][$imgur_consumer_secret]) ){
			
				define('CONSUMER_KEY', 		$this->parameters['value'][$imgur_consumer_key]);
				define('CONSUMER_SECRET', 	$this->parameters['value'][$imgur_consumer_secret]);
				
				// get current action
				
				if(!empty($_REQUEST['action'])){
					
					$this->action = $_REQUEST['action'];
				}
				elseif( $action = $this->parent->session->get_user_data('action') ){
					
					$this->action = $action;
				}
				
				$methodName = 'app'.ucfirst($this->action);

				if(method_exists($this,$methodName)){
					
					$this->$methodName();
				}
			}
			else{
				
				$message = '<div class="alert alert-danger">';
					
					$message .= 'Sorry, imgur is not yet available on this platform, please contact the dev team...';
						
				$message .= '</div>';

				$this->parent->session->update_user_data('message',$message);
			}
		}
	}
	
	public function appImportImg(){
	
		if(!empty($_REQUEST['id'])){
		
			if( $this->app = $this->parent->apps->getAppData( $_REQUEST['id'], $this->parent->user->ID, true ) ){
				
				$client = new \Imgur\Client();
				$client->setOption('client_id', CONSUMER_KEY);
				$client->setOption('client_secret', CONSUMER_SECRET);
				
				$client->setAccessToken($this->app);		

				if($client->checkAccessTokenExpired()) {
					
					$client->refreshToken();
				}

				$images = $client->api('account')->images();

				$urls = [];
				
				if(!empty($images)){
					
					foreach($images as $image){
						
						if(!empty($image['link'])){
							
							$img_title	= basename($image['link']);
							$img_url	= $image['link'];
							
							if(!get_page_by_title( $img_title, OBJECT, 'user-image' )){
								
								if($image_id = wp_insert_post(array(
							
									'post_author' 	=> $this->parent->user->ID,
									'post_title' 	=> $img_title,
									'post_content' 	=> $img_url,
									'post_type' 	=> 'user-image',
									'post_status' 	=> 'publish'
								))){
									
									wp_set_object_terms( $image_id, $this->term->term_id, 'app-type' );
								}
							}						
						}
					}
				}
			}
		}
	}
	
	public function appConnect(){

		$client = new \Imgur\Client();
		$client->setOption('client_id', CONSUMER_KEY);
		$client->setOption('client_secret', CONSUMER_SECRET);

		if( isset($_REQUEST['action']) ){
			
			if( !$token = $this->parent->session->get_user_data('token') ){

				$this->parent->session->update_user_data('app','imgur');
				$this->parent->session->update_user_data('action',$_REQUEST['action']);
				$this->parent->session->update_user_data('ref',( !empty($_REQUEST['ref']) ? $this->parent->request->proto . urldecode($_REQUEST['ref']) : ''));

				$this->oauth_url = $client->getAuthenticationUrl();
			
				wp_redirect($this->oauth_url);
				echo 'Redirecting imgur oauth...';
				exit;
			}			
		}
		elseif( $action = $this->parent->session->get_user_data('action') ){
			
			if( !$access_token = $this->parent->session->get_user_data('access_token') ){
				
				// handle connect callback
				
				if(isset($_REQUEST['code'])){
					
					//get access_token
					
					$client->requestAccessToken($_REQUEST['code']);
					
					$this->access_token = $client->getAccessToken();
					
					$this->reset_session();					
					
					//store access_token in session					
				
					$this->parent->session->update_user_data('access_token',$this->access_token);
					
					if(!empty($this->access_token['account_username'])){

						// store access_token in database		
						
						$app_title = wp_strip_all_tags( 'imgur - ' . $this->access_token['account_username'] );
						
						$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );
						
						if( empty($app_item) ){
							
							// create app item
							
							$app_id = wp_insert_post(array(
							
								'post_title'   	 	=> $app_title,
								'post_status'   	=> 'publish',
								'post_type'  	 	=> 'user-app',
								'post_author'   	=> $this->parent->user->ID
							));
							
							wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );
							
							// hook connected app
							
							do_action( 'ltple_imgur_account_connected');
							
							$this->parent->apps->newAppConnected();							
						}
						else{

							$app_id = $app_item->ID;
						}
							
						// update app item
							
						update_post_meta( $app_id, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));
					}
					
					if( $redirect_url = $this->parent->session->get_user_data('ref') ){

						wp_redirect($redirect_url);
						echo 'Redirecting imgur callback...';
						exit;	
					}
					else{
						
						// store success message

						$message = '<div class="alert alert-success">';
							
							$message .= 'Congratulations, you have successfully connected an Imgur account!';
								
						$message .= '</div>';

						$this->parent->session->update_user_data('message',$message);
					}
				}
				else{
						
					//flush session
						
					$this->reset_session();
				}	
			}
		}
	}
	
	public function reset_session(){
		
		$this->parent->session->update_user_data('token','');
		$this->parent->session->update_user_data('access_token','');
		$this->parent->session->update_user_data('ref',$this->get_ref_url());		
		
		return true;
	}	
} 