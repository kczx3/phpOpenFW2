<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Cache Core Driver Object
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Cache\Drivers;

//**************************************************************************************
/**
 * Core Class
 */
//**************************************************************************************
abstract class Core
{
	//**********************************************************************************
	// Class Members
	//**********************************************************************************
    protected $_namespace = false;
	protected $server = '127.0.0.1';
	protected $port = false;
	protected $weight = 1;
    protected $cache_obj = false;

	//**********************************************************************************
	// Constructor Method
	//**********************************************************************************
    public function __construct($params)
    {
        $this->_namespace = $params['namespace'];
        if (isset($params['server'])) {
            if (is_array($this->server) && !count($this->server)) {
                throw new \Exception('Empty server list passed.');
            }
            else if (empty($this->server)) {
                throw new \Exception('Invalid server given.');
            }
            $this->server = $params['server'];
        }
        if (isset($params['port'])) {
            $this->port = $params['port'];
        }
        if (isset($params['weight'])) {
            $this->weight = $params['weight'];
        }
    }

	//**********************************************************************************
	// Set Option Method
	//**********************************************************************************
	public function setOption($key, $opt)
	{
        return $this->cache_obj->setOption($key, $opt);
	}

	//**********************************************************************************
	// Set Options Method
	//**********************************************************************************
	public function setOptions(Array $opts)
	{
        $opts_set = 0;
        foreach ($opts as $opt_key => $opt_val) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($opt_key)) {
                if ($this->setOption($opt_key, $opt_val)) {
                    $opts_set++;
                }
            }
        }
        return $opts_set;
	}

	//**********************************************************************************
	// Get Option Method
	//**********************************************************************************
	public function getOption($key)
	{
        return $this->cache_obj->getOption($key);
	}

	//**********************************************************************************
	// Get Options Method
	//**********************************************************************************
	public function getOptions(Array $keys)
	{
        $ret_vals = [];
        foreach ($keys as $key) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($key)) {
                $ret_vals[$key] = $this->getOption($key);
            }
        }
        return $ret_vals;
	}

	//**********************************************************************************
	// Set Method
	//**********************************************************************************
	public function set($key, $data, $ttl=0, Array $args=[])
	{
        return $this->cache_obj->set($key, $data, $ttl);
	}

	//**********************************************************************************
	// Set Multiple Method
	//**********************************************************************************
	public function setMulti(Array $values, $ttl=0, Array $args=[])
	{
        $vals_set = 0;
        foreach ($values as $val_key => $val_val) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($val_key)) {
                if ($this->set($val_key, $val_val, $ttl)) {
                    $vals_set++;
                }
            }
        }
        return $vals_set;
	}

	//**********************************************************************************
	// Get Method
	//**********************************************************************************
	public function get($key, Array $args=[])
	{
        if (\phpOpenFW\Cache\Cache::IsValidKey($key)) {
            return $this->cache_obj->get($key);
        }
        return false;
	}

	//**********************************************************************************
	// Get Multiple Method
	//**********************************************************************************
	public function getMulti(Array $keys, Array $args=[])
	{
        $ret_vals = [];
        foreach ($keys as $key) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($key)) {
                $ret_vals[$key] = $this->get($key, $args);
            }
        }
        return $ret_vals;
	}

	//**********************************************************************************
	// Delete Method
	//**********************************************************************************
	public function delete($key, Array $args=[])
	{
        if (\phpOpenFW\Cache\Cache::IsValidKey($key)) {
            return $this->cache_obj->delete($key);
        }
        return false;
	}

	//**********************************************************************************
	// Delete Multiple Method
	//**********************************************************************************
	public function deleteMulti(Array $keys, Array $args=[])
	{
        $vals_deleted = 0;
        foreach ($vals_deleted as $val_key => $val_val) {
            if (\phpOpenFW\Cache\Cache::IsValidKey($val_key)) {
                if ($this->delete($val_key)) {
                    $vals_deleted++;
                }
            }
        }
        return $vals_deleted;
	}

}
