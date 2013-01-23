<?php

require_once "Dropbox/autoload.php";

/**
 * Adapter wrapper for Dropbox-php PEAR
 * https://github.com/Dropbox-PHP/
 *
 * @package Packrat_Cloud
 * @author Rob Vella <wx@wxcs.com>
 *
 */
class Packrat_Cloud_StorageService_Adapter_Dropbox
	implements Zend_Cloud_StorageService_Adapter
{
	const ROOT = 'root';

	/**
	 * Dropbox_API client
	 *
	 * @var Dropbox_API
	 */
	private $_dropboxClient = null;

	public function __construct($options = array())
	{
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            throw new Zend_Cloud_StorageService_Exception('Invalid options provided');
        }

        if (empty($options['oauthClient'])) {
        	throw new Zend_Cloud_StorageService_Exception('No OAuth Client passed. Please handle OAuth authentication and authorization before initializing class');
        }

        // Setup Dropbox Client
        $this->_dropboxClient = new Dropbox_API($options['oauthClient'], $options[self::ROOT]);
	}

    /**
     * Get an item from the given Dropbox path
     *
     * @param  string $path
     * @param  array $options
     * @return mixed
     */
    public function fetchItem($path, $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	return $this->_dropboxClient->getFile($path, $options[self::ROOT]);
    }

    /**
     * Store an item in Dropbox path
	 *
     * @param string $destinationPath
     * @param mixed  $data
     * @param  array $options
     * @return boolean
     */
    public function storeItem($destinationPath,
                              $data,
                              $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	// Store data in temp resource
    	$file = tmpfile();
    	fwrite($file, $data);

    	$this->_dropboxClient->putFile($destinationPath, $file, $options[self::ROOT]);

    	fclose($file);
    }

    /**
     * Delete an item in Dropbox path
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteItem($path, $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	return $this->_dropboxClient->delete($path, $options[self::ROOT]);
    }

    /**
     * Copy an item in Dropbox to a given path.
     *
     * The $destinationPath must be a directory.
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function copyItem($sourcePath, $destinationPath, $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	return $this->_dropboxClient->copy($sourcePath, $destinationPath, $options[self::ROOT]);
    }

    /**
     * Move an item in Dropbox to a given path in Dropbox.
     *
     * The $destinationPath must be a directory.
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function moveItem($sourcePath, $destinationPath, $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	return $this->_dropboxClient->move($sourcePath, $destinationPath, $options[self::ROOT]);
    }

    /**
     * Rename an item in Dropbox path to a given name.
     *
     * @param  string $path
     * @param  string $name
     * @param  array $options
     * @return void
     */
    public function renameItem($path, $name, $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	return $this->moveItem($path, $path."/".$name, $options[self::ROOT]);
    }

    /**
     * List items in the given directory in Dropbox path
     *
     * The $path must be a directory
     *
     * @param  string $path Must be a directory
     * @param  array $options
     * @return array A list of item names
     */
    public function listItems($path, $options = null)
    {
    	$options[self::ROOT] = isset($options[self::ROOT]) ? $options[self::ROOT] : null;

    	return $this->_dropboxClient->getMetaData($path, false, null, $options[self::ROOT]);
    }

    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return array
     */
    public function fetchMetadata($path, $options = null)
    {
    	$options['list'] = isset($options['list']) ? $options['list'] : true;
    	$options['hash'] = isset($options['hash']) ? $options['hash'] : null;
    	$options['fileLimit'] = isset($options['fileLimit']) ? $options['fileLimit'] : null;

    	return $this->_dropboxClient->getMetaData($path, $options['list'], $options['hash'], $options['fileLimit']);
    }

    /**
	 * Method required by interface. No comparable method on API.
     *
     * @param  string $destinationPath
     * @param  array $metadata
     * @param  array $options
     * @return void
     */
    public function storeMetadata($destinationPath, $metadata, $options = null)
    {
    	return;
    }

    /**
     * Method required by interface. No comparable method on API.
     *
     * @param  string $path
     * @return void
     */
    public function deleteMetadata($path)
    {
    	return;
    }

    /**
     * Get the concrete class for Dropbox API
	 *
	 * @return Dropbox_API
     */
    public function getClient()
    {
    	return $this->_dropboxClient;
    }
}
