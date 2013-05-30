<?php

class Auth_Controller extends Base_Controller
{
	public $restful = true;

	/**
	 * OAuth callback method for oneauth bundle
	 */
	public function get_social_login()
	{
		$user_data = Session::get('oneauth');

		$user = User::where_username($user_data['info']['email'])->first();
		
		if ($user) {
			Auth::login($user->id, true);
			Event::fire('oneauth.sync', array($user->id));
			
			// Sync data w/ Facebook
			$user->name = $user_data['info']['name'];
			
			// Refactor. Shit sucks.
			if (isset($user_data['info']['image'])) {
				// Get Profile Pic then save
				$img     = file_get_contents($user_data['info']['image']);
				
				$tempName = tempnam(sys_get_temp_dir(),'profile');
				file_put_contents($tempName, $img);
				
				$imgInfo = getimagesize($tempName);
				
				switch ($imgInfo['mime']) {
					case "image/gif":
						$ext = 'gif';
						break;
					case "image/jpeg":
						$ext = "jpg";
						break;
					case "image/png":
						$ext = "png";
						break;
				}

				$storedImagePath = "public/img/users/";
				$storedImage = md5_file($tempName).".".$ext;
				
				if (!file_exists($storedImagePath.$storedImage)) {
					File::put($storedImagePath.$storedImage, $img);
					$user->img = $storedImage;
				}
			}
			
			$user->save();
			return Redirect::to('/');
		} else {
			return Redirect::to('/login')->with('login_errors', true);			
		}
	}

	/**
	 * Register user from OAuth data
	 */
	public function get_social_register()
	{
		$user_data = Session::get("oneauth");

		$exists = User::where_username($user_data['info']['email'])->first();
		
		if (!$exists) {
			$user = new User;
			
			$user->username = $user_data['info']['email'];
			$user->name     = $user_data['info']['name'];
			$user->save();
			
			Auth::login($user->id, true);
		}
		
		Event::fire('oneauth.sync', array($user->id));
		return OneAuth\Auth\Core::redirect('registered');
	}

	/**
	 * Register page
	 */
	public function get_register()
	{
		return View::make('auth.register');
	}
	
	/**
	 * Do registration
	 */
	public function post_register()
	{
		$rules = array(
			'username' => array('required', 'email', 'unique:users,username'),
			'password' => array('required', 'confirmed', 'min:6'),
			'name' => array('required', 'min:3'),
		);
		
		$validation = Validator::make(Input::all(), $rules);
		
		if ($validation->fails()) {
			return Redirect::to('register')->with_input()->with_errors($validation);
		}
		
		// Good
		$user = new User;
		
		$user->name = Input::get('name');
		$user->username = Input::get('username');
		$user->password = Hash::make(Input::get('password'));
		
		$user->save();
		
		Auth::login($user->id);
		
		return Redirect::to("/");
	}
	
	/**
	 * Login Page
	 */
	public function get_login()
	{
		if (Auth::check()) {
			return Redirect::to("/");
		}
		
		return View::make('auth.login');
	}
	
	/**
	 * Do login
	 */
	public function post_login()
	{
		$userdata = array(
			'username' => Input::get('username'),
			'password' => Input::get('password')
		);

		if (Auth::attempt($userdata)) {
			return Redirect::to('home');
		} else {
	        return Redirect::to('login')
            	->with('login_errors', true);
	    }
	}
	
	/**
	 * Logout page
	 */
	public function get_logout()
	{
		Auth::logout();
		return Redirect::to('/');
	}
}
